<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TapPark - Admin Login</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Login CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css') ?>">
    
    <!-- Optional: Background image (uncomment if you have a background image) -->
 
    <style>
        body {
            background-image: url('<?= base_url('assets/images/loginpagewall.jpg') ?>');
        }
        [data-bs-theme="dark"] body {
            background-image: url('<?= base_url('assets/images/loginpagewall.jpg') ?>');
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100%; width: 100%; margin: 0;">
            <div class="col-12 col-md-8 col-lg-5 d-flex justify-content-center">
                <div class="login-container">
                    <!-- Login Header -->
                    <div class="login-header">
                        <!-- Logo (add your logo path here) -->
                        <img src="<?= base_url('assets/images/LOGOTAPPARK.png') ?>" alt="TapPark Logo" class="login-logo" onerror="this.style.display='none'">
                        <h1>TapPark</h1>
                        <p>Administration System</p>
                    </div>
                    
                    <!-- Login Form -->
                    <?= form_open('login/process', ['id' => 'loginForm']) ?>
                        <!-- Email Input -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required autofocus>
                            <div class="invalid-feedback d-block" id="email-error" style="display: none !important;"></div>
                            <div class="text-danger small mt-1" id="email-error-text" style="display: none;"></div>
                        </div>
                        
                        <!-- Password Input -->
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Show/Hide Password">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback d-block" id="password-error" style="display: none !important;"></div>
                            <div class="text-danger small mt-1" id="password-error-text" style="display: none;"></div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                                <span id="loginText">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </span>
                            </button>
                        </div>
                    <?= form_close() ?>
                    
                    <!-- Alert Messages Container (for displaying success/error messages) -->
                    <div id="alertContainer" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Access Denied Modal (for non-admin users with valid credentials) -->
    <div class="modal fade" id="accessDeniedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-ban me-2"></i>
                        Access Denied
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body text-center py-4">
                    <!-- Warning Icon -->
                    <div class="mb-3">
                        <div class="access-denied-icon-wrapper">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                    
                    <!-- Message -->
                    <h5 class="mb-2 fw-semibold">Access Restricted</h5>
                    <p class="text-muted mb-1">
                        You do not have permission to access this system.
                    </p>
                    <p class="text-muted small mb-0">
                        Only administrators are allowed to log in.
                    </p>
                </div>
                
                <!-- Modal Footer -->
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Invalid Credentials Modal (for wrong email/password) -->
    <div class="modal fade" id="invalidCredentialsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Invalid Credentials
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body text-center py-4">
                    <!-- Warning Icon -->
                    <div class="mb-3">
                        <div class="invalid-credentials-icon-wrapper">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    
                    <!-- Message -->
                    <h5 class="mb-2 fw-semibold">Login Failed</h5>
                    <p class="text-muted mb-1">
                        The email or password you entered is incorrect.
                    </p>
                    <p class="text-muted small mb-0">
                        Please check your credentials and try again.
                    </p>
                </div>
                
                <!-- Modal Footer -->
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery (from CDN) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Access Denied Modal Styles -->
    <style>
    #accessDeniedModal .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    #accessDeniedModal .modal-header {
        border: none;
        padding: 1rem 1.5rem;
    }

    #accessDeniedModal .modal-title {
        font-weight: 600;
    }

    #accessDeniedModal .modal-body {
        padding: 2rem 1.5rem 1rem;
    }

    #accessDeniedModal .access-denied-icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border: 2px solid rgba(220, 53, 69, 0.2);
    }

    #accessDeniedModal .access-denied-icon-wrapper i {
        font-size: 2rem;
        color: #dc3545;
    }

    #accessDeniedModal .modal-footer {
        padding: 1rem 1.5rem 1.5rem;
    }

    #accessDeniedModal .modal-footer .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1.5rem;
    }

    /* Invalid Credentials Modal Styles */
    #invalidCredentialsModal .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    #invalidCredentialsModal .modal-header {
        border: none;
        padding: 1rem 1.5rem;
    }

    #invalidCredentialsModal .modal-title {
        font-weight: 600;
    }

    #invalidCredentialsModal .modal-body {
        padding: 2rem 1.5rem 1rem;
    }

    #invalidCredentialsModal .invalid-credentials-icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border: 2px solid rgba(220, 53, 69, 0.2);
    }

    #invalidCredentialsModal .invalid-credentials-icon-wrapper i {
        font-size: 2rem;
        color: #dc3545;
    }

    #invalidCredentialsModal .modal-footer {
        padding: 1rem 1.5rem 1.5rem;
    }

    #invalidCredentialsModal .modal-footer .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1.5rem;
    }

    /* Error message labels - styled like validation errors */
    #password-error-text,
    #email-error-text {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
        font-weight: 400;
    }

    [data-bs-theme="dark"] #password-error-text,
    [data-bs-theme="dark"] #email-error-text {
        color: #ff5252;
    }

    .error-label {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }

    /* Dark Mode */
    [data-bs-theme="dark"] #accessDeniedModal .modal-content {
        background: var(--card-bg, #ffffff);
    }

    [data-bs-theme="dark"] #accessDeniedModal .access-denied-icon-wrapper {
        background: linear-gradient(135deg, #3a2020 0%, #4a2a2a 100%);
        border-color: rgba(220, 53, 69, 0.3);
    }

    [data-bs-theme="dark"] #accessDeniedModal h5 {
        color: var(--text-color, #333333);
    }

    /* Dark Mode for Invalid Credentials Modal */
    [data-bs-theme="dark"] #invalidCredentialsModal .modal-content {
        background: var(--card-bg, #ffffff);
    }

    [data-bs-theme="dark"] #invalidCredentialsModal .invalid-credentials-icon-wrapper {
        background: linear-gradient(135deg, #3a2020 0%, #4a2a2a 100%);
        border-color: rgba(220, 53, 69, 0.3);
    }

    [data-bs-theme="dark"] #invalidCredentialsModal h5 {
        color: var(--text-color, #333333);
    }
    </style>

    <!-- Login JavaScript (Basic - Only password toggle for now) -->
    <script>
        $(document).ready(function() {
            // Load saved theme from localStorage (if user previously set theme)
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            
            // Password Toggle Functionality
            $('#togglePassword').on('click', function() {
                const passwordInput = $('#password');
                const passwordIcon = $('#togglePasswordIcon');
                const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
                
                passwordInput.attr('type', type);
                
                // Toggle icon between eye and eye-slash
                if (type === 'text') {
                    passwordIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            // Form submission handler with validation and AJAX
            $('#loginForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission
                
                // Clear previous errors
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('').hide();
                $('#password-error-text').text('').hide();
                $('#email-error-text').text('').hide();
                $('#alertContainer').empty();
                
                // Show loading state
                $('#loginBtn').prop('disabled', true);
                $('#loginText').html('<span class="spinner-border spinner-border-sm me-2"></span>Signing In...');
                
                // Get form data
                const formData = {
                    email: $('#email').val().trim(),
                    password: $('#password').val()
                };
                
                // Add CSRF token to form data
                const csrfToken = $('input[name="csrf_test_name"]').val();
                if (csrfToken) {
                    formData.csrf_test_name = csrfToken;
                }
                
                // Client-side validation
                let hasError = false;
                
                // Validate email
                if (!formData.email) {
                    $('#email').addClass('is-invalid');
                    $('#email-error-text').text('Email is required').show();
                    hasError = true;
                } else if (!isValidEmail(formData.email)) {
                    $('#email').addClass('is-invalid');
                    $('#email-error-text').text('Please enter a valid email address').show();
                    hasError = true;
                }
                
                // Validate password
                if (!formData.password) {
                    $('#password').addClass('is-invalid');
                    $('#password-error-text').text('Password is required').show();
                    hasError = true;
                } else if (formData.password.length < 6) {
                    $('#password').addClass('is-invalid');
                    $('#password-error-text').text('Password must be at least 6 characters long').show();
                    hasError = true;
                }
                
                // If validation errors, stop submission
                if (hasError) {
                    $('#loginBtn').prop('disabled', false);
                    $('#loginText').html('<i class="fas fa-sign-in-alt me-2"></i>Sign In');
                    return;
                }
                
                // Submit login request via AJAX
                $.ajax({
                    url: '<?= base_url('login/process') ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    statusCode: {
                        401: function(xhr) {
                            // Handle 401 Unauthorized (invalid credentials - wrong email/password)
                            let response = xhr.responseJSON;
                            let errorMessage = 'Invalid email or password';
                            
                            // Try to parse response if not already parsed
                            if (!response && xhr.responseText) {
                                try {
                                    response = JSON.parse(xhr.responseText);
                                } catch (e) {
                                    // If parsing fails, use default message
                                }
                            }
                            
                            // Use message from response if available
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                            
                            // Show error as label below password field (like validation errors)
                            $('#email').addClass('is-invalid');
                            $('#password').addClass('is-invalid');
                            $('#email-error').text('').hide();
                            $('#password-error').text('').hide();
                            $('#email-error-text').text('').hide();
                            $('#password-error-text').text(errorMessage).show();
                            
                            // Reset button state
                            $('#loginBtn').prop('disabled', false);
                            $('#loginText').html('<i class="fas fa-sign-in-alt me-2"></i>Sign In');
                        },
                        403: function(xhr) {
                            // Handle 403 Forbidden (valid credentials but not admin)
                            let response = xhr.responseJSON;
                            let errorMessage = 'Access denied. Admin privileges required.';
                            
                            // Try to parse response if not already parsed
                            if (!response && xhr.responseText) {
                                try {
                                    response = JSON.parse(xhr.responseText);
                                } catch (e) {
                                    // If parsing fails, use default message
                                }
                            }
                            
                            // Use message from response if available
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                            
                            // Show error as label below password field (like validation errors)
                            $('#email').addClass('is-invalid');
                            $('#password').addClass('is-invalid');
                            $('#email-error').text('').hide();
                            $('#password-error').text('').hide();
                            $('#email-error-text').text('').hide();
                            $('#password-error-text').text(errorMessage).show();
                            
                            // Reset button state
                            $('#loginBtn').prop('disabled', false);
                            $('#loginText').html('<i class="fas fa-sign-in-alt me-2"></i>Sign In');
                        },
                        422: function(xhr) {
                            // Handle 422 Validation Error
                            let response = xhr.responseJSON;
                            let errorMessage = 'Please check your input and try again';
                            
                            // Try to parse response if not already parsed
                            if (!response && xhr.responseText) {
                                try {
                                    response = JSON.parse(xhr.responseText);
                                } catch (e) {
                                    // If parsing fails, use default message
                                }
                            }
                            
                            // Use message from response if available
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                            
                            // Show field errors if any
                            if (response && response.errors) {
                                Object.keys(response.errors).forEach(function(field) {
                                    $('#' + field).addClass('is-invalid');
                                    if (field === 'password') {
                                        $('#password-error-text').text(response.errors[field]).show();
                                    } else if (field === 'email') {
                                        $('#email-error-text').text(response.errors[field]).show();
                                    } else {
                                        $('#' + field + '-error').text(response.errors[field]).show();
                                    }
                                });
                            }
                            
                            showAlert('danger', '<i class="fas fa-exclamation-circle me-2"></i>' + errorMessage);
                            $('#loginBtn').prop('disabled', false);
                            $('#loginText').html('<i class="fas fa-sign-in-alt me-2"></i>Sign In');
                        },
                        429: function(xhr) {
                            // Handle 429 Too Many Requests (rate limited)
                            let response = xhr.responseJSON;
                            let errorMessage = 'Too many login attempts. Please try again later.';
                            
                            if (!response && xhr.responseText) {
                                try {
                                    response = JSON.parse(xhr.responseText);
                                } catch (e) {
                                    // If parsing fails, use default message
                                }
                            }
                            
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                            
                            // Show rate limit error prominently
                            showAlert('danger', '<i class="fas fa-clock me-2"></i>' + errorMessage);
                            $('#loginBtn').prop('disabled', false);
                            $('#loginText').html('<i class="fas fa-sign-in-alt me-2"></i>Sign In');
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            showAlert('success', '<i class="fas fa-check-circle me-2"></i>' + response.message);
                            
                            // Redirect after short delay
                            setTimeout(function() {
                                // Redirect to root which loads main_layout.php
                                window.location.href = response.redirect || '<?= base_url() ?>';
                            }, 1000);
                        } else {
                            // Show error message with text labels
                            let errorMessage = response.message || 'An error occurred. Please try again.';
                            
                            // If it's a password/email error, show it on the fields
                            if (errorMessage.toLowerCase().includes('password') || errorMessage.toLowerCase().includes('email') || errorMessage.toLowerCase().includes('invalid')) {
                                $('#email').addClass('is-invalid');
                                $('#password').addClass('is-invalid');
                                $('#email-error').text('').hide();
                                $('#password-error').text('').hide();
                                $('#email-error-text').text('').hide();
                                $('#password-error-text').text(errorMessage).show();
                            }
                            
                            // Show alert for visibility
                            showAlert('danger', '<i class="fas fa-exclamation-circle me-2"></i>' + errorMessage);
                            
                            // Show field errors if any
                            if (response.errors) {
                                Object.keys(response.errors).forEach(function(field) {
                                    $('#' + field).addClass('is-invalid');
                                    $('#' + field + '-error').text(response.errors[field]);
                                });
                            }
                            
                            // Reset button state
                            $('#loginBtn').prop('disabled', false);
                            $('#loginText').html('<i class="fas fa-sign-in-alt me-2"></i>Sign In');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle other errors (network errors, 500, etc.)
                        let errorMessage = 'An error occurred. Please try again.';
                        
                        // Only handle if statusCode handlers didn't catch it
                        if (xhr.status !== 401 && xhr.status !== 403 && xhr.status !== 422 && xhr.status !== 429) {
                            // Try to parse error response
                            let response = xhr.responseJSON;
                            if (!response && xhr.responseText) {
                                try {
                                    response = JSON.parse(xhr.responseText);
                                } catch (e) {
                                    // Parsing failed
                                }
                            }
                            
                            if (response && response.message) {
                                errorMessage = response.message;
                            } else if (xhr.status === 500) {
                                errorMessage = 'Server error. Please try again later.';
                            }
                            
                            // Show error message with text labels for password/email errors
                            if (errorMessage.toLowerCase().includes('password') || errorMessage.toLowerCase().includes('email') || errorMessage.toLowerCase().includes('invalid') || errorMessage.toLowerCase().includes('access denied')) {
                                $('#email').addClass('is-invalid');
                                $('#password').addClass('is-invalid');
                                $('#email-error').text('').hide();
                                $('#password-error').text('').hide();
                                $('#email-error-text').text('').hide();
                                $('#password-error-text').text(errorMessage).show();
                            }
                            
                            showAlert('danger', '<i class="fas fa-exclamation-circle me-2"></i>' + errorMessage);
                            $('#loginBtn').prop('disabled', false);
                            $('#loginText').html('<i class="fas fa-sign-in-alt me-2"></i>Sign In');
                        }
                    }
                });
            });
            
            // Email validation helper function
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            
            // Show alert message
            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                $('#alertContainer').html(alertHtml);
                
                // Auto-dismiss after 5 seconds
                setTimeout(function() {
                    $('#alertContainer .alert').fadeOut(function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        });
    </script>
</body>
</html>

