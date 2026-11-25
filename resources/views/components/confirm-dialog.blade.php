<dialog id="pst-confirm-dialog"
        closedby="closerequest"
        aria-labelledby="pst-confirm-title"
        class="pst-dialog max-w-md">
    <div class="border-b border-[#DCE3EC] px-6 py-4">
        <h2 id="pst-confirm-title" data-confirm-title class="text-base font-bold text-[#0F172A]">Konfirmasi Tindakan</h2>
    </div>
    <form method="dialog" class="px-6 py-5 space-y-5">
        <p data-confirm-message class="text-sm leading-relaxed text-[#475569]">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
        <div class="flex items-center justify-end gap-2.5 border-t border-[#DCE3EC] pt-4">
            <button type="submit"
                    value="cancel"
                    data-confirm-cancel
                    autofocus
                    class="rounded-md border border-[#CBD5E1] bg-white px-4 py-2 text-xs font-semibold text-[#334155] hover:bg-[#F4F6F9]">
                Batal
            </button>
            <button type="submit"
                    value="confirm"
                    data-confirm-accept
                    class="rounded-md bg-[#0B4F9C] px-4 py-2 text-xs font-semibold text-white hover:bg-[#083D7A]">
                Lanjutkan
            </button>
        </div>
    </form>
</dialog>
