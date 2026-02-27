<!-- Form Dirty State Check -->
<script>
        (function () {
            const form = document.getElementById('form');
            const submitBtn = form ? form.querySelector('button[type="submit"]') : null;

            if (!form || !submitBtn) return;

            /**
             * Serialize only text-based form entries (skip file inputs).
             * File objects stringify inconsistently across calls,
             * causing false "dirty" detection.
             */
            const getFormDataString = () => {
                const fd = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, value] of fd.entries()) {
                    if (!(value instanceof File)) {
                        params.append(key, value);
                    }
                }
                return params.toString();
            };

            let initialData = getFormDataString();

            // Track whether a file was picked (separately from text fields)
            let fileChanged = false;

            const checkDirtyState = () => {
                if (fileChanged || getFormDataString() !== initialData) {
                    submitBtn.removeAttribute('disabled');
                } else {
                    submitBtn.setAttribute('disabled', 'disabled');
                }
            };

            // Detect file input changes separately
            form.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', () => {
                    fileChanged = input.files.length > 0;
                    checkDirtyState();
                });
            });

            // Initialize: button starts disabled (no changes yet)
            checkDirtyState();

            // Listen for all typing and dropdown changes
            form.addEventListener('input', checkDirtyState);
            form.addEventListener('change', checkDirtyState);

            // Listen for dynamically added/removed reason/lesson rows
            const observer = new MutationObserver(checkDirtyState);
            observer.observe(form, { childList: true, subtree: true });
        })();
</script>