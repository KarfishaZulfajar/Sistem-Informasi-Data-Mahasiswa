'use strict';

document.addEventListener('DOMContentLoaded', () => {
    // Sidebar responsif.
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    const closeSidebar = () => {
        sidebar?.classList.remove('show');
        sidebarOverlay?.classList.remove('show');
    };

    sidebarToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('show');
        sidebarOverlay?.classList.toggle('show');
    });
    sidebarOverlay?.addEventListener('click', closeSidebar);
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) closeSidebar();
    });

    // Tampilkan/sembunyikan password pada halaman login.
    document.querySelectorAll('.toggle-password').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.innerHTML = `<i class="bi ${show ? 'bi-eye-slash' : 'bi-eye'}"></i>`;
            button.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });

    // Validasi Bootstrap dan pencegah klik submit ganda.
    document.querySelectorAll('.needs-validation').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                const firstInvalid = form.querySelector(':invalid');
                firstInvalid?.focus();
            } else {
                const submitButton = form.querySelector('.submit-button');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
                }
            }
            form.classList.add('was-validated');
        });
    });

    // Konfirmasi hapus menggunakan modal Bootstrap yang dibuat oleh JavaScript.
    const deleteModalElement = createDeleteModal();
    const deleteModal = deleteModalElement ? new bootstrap.Modal(deleteModalElement) : null;
    let pendingDeleteForm = null;

    document.querySelectorAll('.delete-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            pendingDeleteForm = form;
            const studentName = form.dataset.name || 'mahasiswa ini';
            const nameTarget = deleteModalElement?.querySelector('[data-student-name]');
            if (nameTarget) nameTarget.textContent = studentName;
            deleteModal?.show();
        });
    });

    deleteModalElement?.querySelector('[data-confirm-delete]')?.addEventListener('click', () => {
        if (!pendingDeleteForm) return;
        const button = pendingDeleteForm.querySelector('button[type="submit"]');
        if (button) button.disabled = true;
        pendingDeleteForm.submit();
    });

    // Input angka saja untuk NIM dan nomor HP.
    document.querySelectorAll('.numeric-only').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '');
        });
    });

    // Filter kelas berdasarkan program studi yang dipilih.
    const prodiSelect = document.getElementById('id_prodi');
    const kelasSelect = document.getElementById('id_kelas');
    if (prodiSelect && kelasSelect) {
        const filterClasses = (preserveSelection = true) => {
            const selectedProdi = prodiSelect.value;
            const currentClass = preserveSelection ? kelasSelect.value : '';
            let currentStillValid = currentClass === '';

            Array.from(kelasSelect.options).forEach((option, index) => {
                if (index === 0) return;
                const visible = selectedProdi !== '' && option.dataset.prodi === selectedProdi;
                option.hidden = !visible;
                option.disabled = !visible;
                if (visible && option.value === currentClass) currentStillValid = true;
            });

            if (!currentStillValid || selectedProdi === '') kelasSelect.value = '';
            kelasSelect.disabled = selectedProdi === '';
        };

        filterClasses(true);
        prodiSelect.addEventListener('change', () => filterClasses(false));
    }

    // Penghitung karakter textarea.
    document.querySelectorAll('[data-counter]').forEach((textarea) => {
        const counter = document.getElementById(textarea.dataset.counter);
        const updateCounter = () => {
            if (counter) counter.textContent = String(textarea.value.length);
        };
        updateCounter();
        textarea.addEventListener('input', updateCounter);
    });

    // Reset form mengembalikan filter kelas dan penghitung karakter.
    document.getElementById('resetForm')?.addEventListener('click', () => {
        setTimeout(() => {
            prodiSelect?.dispatchEvent(new Event('change'));
            document.querySelectorAll('[data-counter]').forEach((textarea) => {
                textarea.dispatchEvent(new Event('input'));
            });
            document.getElementById('studentForm')?.classList.remove('was-validated');
        }, 0);
    });

    // Alert sukses/info otomatis hilang setelah 5 detik.
    document.querySelectorAll('.auto-dismiss').forEach((alertElement) => {
        window.setTimeout(() => {
            bootstrap.Alert.getOrCreateInstance(alertElement).close();
        }, 5000);
    });
});

function createDeleteModal() {
    if (!document.querySelector('.delete-form')) return null;

    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h2 class="modal-title fs-5">Konfirmasi Hapus</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="delete-warning-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <p class="text-center mb-1">Yakin ingin menghapus data</p>
                        <p class="text-center fw-bold mb-0" data-student-name></p>
                        <small class="d-block text-center text-muted mt-2">Tindakan ini tidak dapat dibatalkan.</small>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" data-confirm-delete><i class="bi bi-trash me-1"></i>Hapus</button>
                    </div>
                </div>
            </div>
        </div>`;

    const modal = wrapper.firstElementChild;
    document.body.appendChild(modal);
    return modal;
}
