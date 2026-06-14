<div class="rounded-xl border border-violet-100 bg-violet-50/50 p-4">
    <label class="flex items-start gap-3 text-sm text-slate-700">
        <input type="hidden" name="kvkk_accepted" value="0">
        <input type="checkbox" name="kvkk_accepted" value="1" required class="mt-1 h-4 w-4 rounded border-slate-300 text-violet-600" @checked(old('kvkk_accepted'))>
        <span>
            <a href="#" data-policy-open="clarification" class="font-semibold text-violet-700 underline-offset-2 hover:underline">KVKK aydınlatma metnini</a>
            okudum ve geçici oturum için kişisel verilerimin işlenmesini kabul ediyorum.
            @if(trim((string) $clarificationText) === '')
                <span class="mt-1 block text-xs text-slate-500">Görüntülü boyama hizmeti kapsamında oturum bilgileriniz yalnızca oda süresince kullanılır.</span>
            @endif
        </span>
    </label>
</div>
