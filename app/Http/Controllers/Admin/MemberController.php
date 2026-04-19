<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseSupportTicket;
use App\Models\User;
use App\Support\SiteMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $members = User::query()
            ->where('is_admin', false)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('email', 'like', '%'.$q.'%')
                        ->orWhere('name', 'like', '%'.$q.'%')
                        ->orWhere('first_name', 'like', '%'.$q.'%')
                        ->orWhere('last_name', 'like', '%'.$q.'%');
                });
            })
            ->withCount([
                'cartItems',
                'transactions as paid_transactions_count' => fn ($t) => $t->where('status', 'paid'),
            ])
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.members.index', [
            'members' => $members,
            'search' => $q,
        ]);
    }

    public function show(User $user)
    {
        abort_if($user->is_admin, 404);

        $user->load([
            'cartItems' => fn ($q) => $q->orderByDesc('created_at'),
            'cartItems.coloringPage.category',
            'transactions' => fn ($q) => $q->with('coloringPage')->orderByDesc('created_at'),
            'purchaseSupportTickets' => fn ($q) => $q->with('transaction.coloringPage')->orderByDesc('created_at'),
        ]);

        $cartTotal = $user->cartItems->sum(fn ($item) => (float) ($item->coloringPage->price ?? 0));

        $paidTransactions = $user->transactions->where('status', 'paid')->values();
        $otherTransactions = $user->transactions->where('status', '!=', 'paid')->values();

        return view('admin.members.show', [
            'member' => $user,
            'cartTotal' => $cartTotal,
            'paidTransactions' => $paidTransactions,
            'otherTransactions' => $otherTransactions,
        ]);
    }

    public function replyPurchaseSupport(Request $request, User $user, PurchaseSupportTicket $ticket): RedirectResponse
    {
        abort_if($user->is_admin, 404);
        abort_if((int) $ticket->user_id !== (int) $user->id, 404);

        $data = $request->validate([
            'admin_reply' => ['required', 'string', 'min:2', 'max:8000'],
        ]);

        $ticket->admin_reply = $data['admin_reply'];
        $ticket->admin_replied_at = now();
        $ticket->save();

        $ticket->load('transaction.coloringPage');

        try {
            $this->sendPurchaseSupportReplyEmail($ticket, $user);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.members.show', $user)
                ->with('warning', 'Yanıt kaydedildi ancak üyeye e-posta gönderilemedi. SMTP ayarlarını kontrol edin.');
        }

        return redirect()
            ->route('admin.members.show', $user)
            ->with('success', 'Yanıt kaydedildi ve üyeye e-posta ile gönderildi.');
    }

    private function sendPurchaseSupportReplyEmail(PurchaseSupportTicket $ticket, User $user): void
    {
        $appName = config('app.name', 'Boya Etkinlik');
        $appNameSafe = e($appName);
        $productLine = $ticket->transaction_id
            ? ($ticket->transaction->coloringPage->title ?? 'Satın alımınız')
            : 'Destek talebiniz';
        $productLineSafe = e($productLine);

        $replyHtml = nl2br(e($ticket->admin_reply ?? ''));
        $originalHtml = nl2br(e($ticket->member_message));
        $name = e($user->display_name);

        $html = <<<HTML
<!doctype html>
<html lang="tr">
<head><meta charset="UTF-8"></head>
<body style="font-family:Inter,Arial,sans-serif;color:#0f172a;line-height:1.5;">
    <p>Merhaba {$name},</p>
    <p><strong>{$appNameSafe}</strong> satın alma desteğine yanıt:</p>
    <p style="color:#64748b;font-size:14px;">Konu: {$productLineSafe}</p>
    <div style="margin:16px 0;padding:16px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;">
        {$replyHtml}
    </div>
    <p style="font-size:13px;color:#64748b;">Sizin mesajınız:</p>
    <div style="font-size:13px;color:#94a3b8;">{$originalHtml}</div>
    <p style="margin-top:24px;font-size:12px;color:#94a3b8;">{$appNameSafe}</p>
</body>
</html>
HTML;

        $text = "Merhaba {$user->display_name},\n\n"
            ."Satın alma desteği yanıtı ({$productLine}):\n\n"
            .($ticket->admin_reply ?? '')
            ."\n\n---\nSizin mesajınız:\n".$ticket->member_message;

        SiteMailer::send(
            (string) $user->email,
            'Destek yanıtı — '.$appName,
            $html,
            $text
        );
    }
}
