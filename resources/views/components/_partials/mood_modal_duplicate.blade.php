<turbo-frame id="mood_modal_content">
    <div class="text-center mb-4">
        <div class="d-flex justify-content-center">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 60px; height: 60px;">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier"> 
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="#ffc107"></path>
                    <path d="M12 17C12.5523 17 13 16.5523 13 16C13 15.4477 12.5523 15 12 15C11.4477 15 11 15.4477 11 16C11 16.5523 11.4477 17 12 17ZM11.2929 12.7071L11.2929 7L12.7071 7L12.7071 12.7071L17 17L15.5858 18.4142L12 14.8284L8.41421 18.4142L7 17L11.2929 12.7071Z" fill="black"></path>
                </g>
            </svg>
        </div>
        <p class="mt-2 mb-3" style="font-size: 1.2rem; color: #856404; font-weight: bold;">Sudah Disimpan!</p>
    </div>
    <hr style="border-color: #dc3545; border-width: 2px;">
    <div class="text-center">
        <p class="mb-0">Kamu sudah mencatat perasaanmu hari ini.</p>
        <p class="mb-0">Kamu hanya bisa menyimpan satu catatan per hari.</p>
    </div>
</turbo-frame>
<script>
    // Tutup modal secara otomatis setelah 3 detik
    setTimeout(() => {
        closeMoodModal();
    }, 3000);
</script>