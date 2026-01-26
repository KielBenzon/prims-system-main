// Form Validation Helper
window.FormValidator = {
    maxFileSize: 10 * 1024 * 1024, // 10MB in bytes (upgraded limit)
    
    // Show error text under file input
    showError(message, fileInput = null) {
        // Show red text under file input if provided
        if (fileInput) {
            // Remove any existing error messages
            const existingErrors = document.querySelectorAll('.file-size-error-text');
            existingErrors.forEach(error => error.remove());
            
            // Create error text element
            const errorText = document.createElement('div');
            errorText.className = 'file-size-error-text text-red-600 text-sm font-semibold mt-2 flex items-center gap-2';
            errorText.innerHTML = `
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>${message}</span>
            `;
            
            // Insert after file input
            fileInput.parentNode.insertBefore(errorText, fileInput.nextSibling);
        }
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
    console.log('File validator loaded - max size: 10MB');
    
    const fileInputs = document.querySelectorAll('input[type="file"]');
    console.log('Found ' + fileInputs.length + ' file inputs');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            console.log('File selected:', this.files[0]?.name, 'Size:', this.files[0]?.size);
            
            // Remove any existing error text first
            const existingErrors = document.querySelectorAll('.file-size-error-text');
            existingErrors.forEach(error => error.remove());
            
            if (!this.files || this.files.length === 0) {
                this.classList.remove('border-red-500', 'border-2', 'border-green-500');
                return;
            }
            
            const file = this.files[0];
            const maxSize = FormValidator.maxFileSize;
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            const maxSizeMB = (maxSize / (1024 * 1024)).toFixed(0);
            
            console.log('Checking file size:', fileSizeMB + 'MB', 'Max:', maxSizeMB + 'MB');
            
            if (file.size > maxSize) {
                FormValidator.showError(`File too large (${fileSizeMB}MB). Maximum allowed: ${maxSizeMB}MB. Please choose a smaller file.`, this);
                this.value = ''; // Clear the input
                this.classList.add('border-red-500', 'border-2');
                this.classList.remove('border-green-500');
                e.preventDefault();
                return false;
            } else {
                this.classList.remove('border-red-500', 'border-2');
                this.classList.add('border-green-500');
            }
        });
    });
});
