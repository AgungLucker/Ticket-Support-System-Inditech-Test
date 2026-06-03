

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const ATTACHMENT_ALLOWED_TYPES = [
    'image/jpeg', 'image/png', 'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];

Alpine.data('attachmentValidator', () => ({
    errors: [],
    validate(event) {
        const files = event.target.files;
        this.errors = [];

        if (files.length > 5) {
            this.errors.push('Maksimal 5 file yang diizinkan.');
        }

        Array.from(files).forEach(file => {
            if (file.size > 2 * 1024 * 1024) {
                this.errors.push(`"${file.name}" melebihi batas 2MB (${(file.size / 1024 / 1024).toFixed(2)}MB).`);
            }
            if (!ATTACHMENT_ALLOWED_TYPES.includes(file.type)) {
                this.errors.push(`"${file.name}" format tidak didukung.`);
            }
        });

        if (this.errors.length > 0) {
            event.target.value = '';
        }
    },
}));

Alpine.start();
