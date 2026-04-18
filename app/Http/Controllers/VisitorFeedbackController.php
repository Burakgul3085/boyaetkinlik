<?php

namespace App\Http\Controllers;

use App\Models\VisitorFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VisitorFeedbackController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:4000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ], [
            'first_name.required' => 'İsim zorunludur.',
            'last_name.required' => 'Soyad zorunludur.',
            'email.required' => 'E-posta zorunludur.',
            'body.required' => 'Mesaj zorunludur.',
            'body.min' => 'Mesaj en az 10 karakter olmalıdır.',
            'rating.required' => 'Yıldız değerlendirmesi zorunludur.',
        ]);

        VisitorFeedback::query()->create([
            'first_name' => $request->string('first_name')->toString(),
            'last_name' => $request->string('last_name')->toString(),
            'email' => $request->string('email')->toString(),
            'body' => $request->string('body')->toString(),
            'rating' => (int) $request->input('rating'),
            'is_approved' => false,
            'show_email_public' => false,
            'admin_reply_published' => false,
        ]);

        return redirect()->to(url()->route('home').'#ziyaretci-geri-bildirim')
            ->with('feedback_success', 'Teşekkürler. Mesajınız incelemeye alındı; onaylandığında bu bölümde yayınlanacaktır.');
    }
}
