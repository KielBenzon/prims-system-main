// Form Validation Helper
window.FormValidator = {
    maxFileSize: 2 * 1024 * 1024, // 2MB in bytes (Azure limit)
    
    // Show error notification
    showError(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'alert alert-error fixed top-4 right-4 z-50 max-w-md shadow-lg';
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    },
    
    // Validate required fields
    validateRequiredFields(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;
        
        const requiredInputs = form.querySelectorAll('[required]');
        const emptyFields = [];
        
        requiredInputs.forEach(input => {
            const label = form.querySelector(`label[for="${input.id}"]`)?.textContent || input.name;
            
            if (input.type === 'file') {
                if (!input.files || input.files.length === 0) {
                    emptyFields.push(label);
                    input.classList.add('border-red-500');
                }
            } else if (!input.value.trim()) {
                emptyFields.push(label);
                input.classList.add('border-red-500');
            } else {
                input.classList.remove('border-red-500');
            }
        });
        
        if (emptyFields.length > 0) {
            this.showError(`Please fill in: ${emptyFields.join(', ')}`);
            return false;
        }
        
        return true;
    },
    
    // Validate file size
    validateFileSize(fileInput, customMaxSize = null) {
        const maxSize = customMaxSize || this.maxFileSize;
        
        if (!fileInput.files || fileInput.files.length === 0) {
            return true; // No file selected
        }
        
        const file = fileInput.files[0];
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        const maxSizeMB = (maxSize / (1024 * 1024)).toFixed(0);
        
        if (file.size > maxSize) {
            this.showError(`File too large (${fileSizeMB}MB). Maximum size: ${maxSizeMB}MB`);
            fileInput.value = '';
            fileInput.classList.add('border-red-500');
            return false;
        }
        
        fileInput.classList.remove('border-red-500');
        return true;
    },
    
    // Validate form before submission
    validateForm(formId, options = {}) {
        const { 
            maxFileSize = this.maxFileSize,
            fileInputId = null,
            skipFileValidation = false 
        } = options;
        
        // Validate required fields
        if (!this.validateRequiredFields(formId)) {
            return false;
        }
        
        // Validate file size if file input specified
        if (fileInputId && !skipFileValidation) {
            const fileInput = document.getElementById(fileInputId);
            if (fileInput && !this.validateFileSize(fileInput, maxFileSize)) {
                return false;
            }
        }
        
        return true;
    }
};

// Real-time file size validation
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            FormValidator.validateFileSize(this);
        });
    });
});
