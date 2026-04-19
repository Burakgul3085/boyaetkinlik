<?php

namespace App\Http\Controllers;

use App\Models\PurchaseSupportTicket;
use App\Models\Setting;
use App\Models\Transaction;
use App\Support\SiteMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class MemberPurchaseSupportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'transaction_id' => ['nullable', 'integer', 'exists:transactions,id'],
            'member_message' => ['required', 'string', 'min:10', 'max:4000'],
        ]);

        $transactionId = $data['transaction_id'] ?? null;

        if ($transactionId !== null) {
            $transaction = Transaction::query()->findOrFail($transactionId);
            abort_if($transaction->status !== 'paid', 403);
            abort_if(
                (int) ($transaction->user_id ?? 0) !== (int) $user->id
                && ! ((int) ($transaction->user_id ?? 0) === 0 && $transaction->email === $user->email),
                403
            );
        }

        $ticket = PurchaseSupportTicket::query()->create([
            'user_id' => $user->id,
            'transaction_id' => $transactionId,
            'member_message' => $data['member_message'],
        ]);

        $ticket->load('transaction.coloringPage');

        try {
            $this->notifyAdminNewTicket($ticket, $user);
        } catch (Throwable $exception) {
            report($exception);
        }

        return redirect()
            ->route('member.purchases')
            ->with('support_success', true);
    }

    private function notifyAdminNewTicket(PurchaseSupportTicket $ticket, $user): void
    {
        $adminEmail = Setting::getValue('contact_email', '');
        if ($adminEmail === '') {
            return;
        }

        $productLine = $ticket->transaction_id
            ? 'Sipariş: '.($ticket->transaction->order_id ?? '-').' — '.($ticket->transaction->coloringPage->title ?? 'Ürün')
            : 'Genel / sipariş seçilmedi';

        $safeMsg = nl2br(e($ticket->member_message));
        $memberName = e($user->display_name);
        $memberEmail = e($user->email);

        $html = <<<HTML
<!doctype html>
<html lang="tr">
<head><meta charset="UTF-8"></head>
<body style="font-family:Inter,Arial,sans-serif;color:#0f172a;line-height:1.5;">
    <p><strong>Yeni satın alma destek mesajı</strong></p>
    <p>Üye: {$memberName} &lt;{$memberEmail}&gt;</p>
    <p>{$productLine}</p>
    <hr style="border:none;border-top:1px solid #e2e8f0;margin:16px 0;">
    <div>{$safeMsg}</div>
    <p style="margin-top:16px;font-size:12px;color:#64748b;">Yönetim panelinde yanıtlayabilirsiniz.</p>
</body>
</html>
HTML;

        $text = "Yeni satın alma destek mesajı\n\n"
            ."Üye: {$user->display_name} <{$user->email}>\n"
            ."{$productLine}\n\n"
            .$ticket->member_message;

        SiteMailer::send(
            $adminEmail,
            'Satın alma desteği — '.$user->display_name,
            $html,
            $text,
            ['email' => $user->email, 'name' => $user->display_name]
        );
    }
}
