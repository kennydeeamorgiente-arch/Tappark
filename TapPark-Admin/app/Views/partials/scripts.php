<!-- jQuery (deferred to just before other scripts for faster first paint) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- CSRF Helper -->
<script src="<?= base_url('assets/js/csrf-helper.js') ?>"></script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Fallback for Bootstrap JS if CDN fails
if (typeof bootstrap === 'undefined') {
    var script = document.createElement('script');
    script.src = '<?= base_url('assets/js/bootstrap.bundle.min.js') ?>';
    document.head.appendChild(script);
}
</script>

<!-- Chart.js (load once globally before dashboard/reports scripts) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Password strength library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- Base URL must be defined BEFORE reports.js and dashboard.js load -->
<script>
    // Base URL for AJAX requests (must be defined before dashboard.js and reports.js)
    const BASE_URL = '<?= base_url() ?>';
    
    // Also set window.APP_BASE_URL for compatibility
    window.APP_BASE_URL = BASE_URL;

    // Geoapify configuration (frontend autocomplete + map preview)
    window.GEOAPIFY_API_KEY = 'ca1241f2a1f0481493c6614db845a901';

    // Global Application Settings (from session)
    <?php 
        $appSettings = session()->get('app_settings') ?: [
            'timezone' => 'Asia/Manila',
            'records_per_page' => 25
        ];
    ?>
    window.APP_TIMEZONE = '<?= $appSettings['timezone'] ?? 'Asia/Manila' ?>';
    window.APP_RECORDS_PER_PAGE = <?= (int)($appSettings['records_per_page'] ?? 25) ?>;

    // Shared email validation helper
    window.isValidEmailStrict = function(email) {
        if (!email || typeof email !== 'string') return false;
        const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
        return emailRegex.test(email.trim());
    };

    // Shared password strength helper (requires zxcvbn)
    window.PasswordStrength = (function() {
        const levelClasses = ['strength-level-0', 'strength-level-1', 'strength-level-2', 'strength-level-3', 'strength-level-4'];
        const levelTexts = ['Too guessable', 'Very weak', 'Weak', 'Fair', 'Strong'];

        function getElement(target) {
            if (!target) return null;
            if (typeof target === 'string') {
                return document.querySelector(target);
            }
            return target;
        }

        function clearClasses(element) {
            if (!element) return;
            levelClasses.forEach(cls => element.classList.remove(cls));
        }

        function update(inputEl, barEl, textEl) {
            const input = getElement(inputEl);
            const bar = getElement(barEl);
            const text = getElement(textEl);

            if (!input || !bar) return;

            const value = input.value || '';
            const hasZxcvbn = typeof window.zxcvbn === 'function';
            const score = hasZxcvbn ? window.zxcvbn(value).score : Math.min(Math.floor(value.length / 4), 4);
            const width = value ? ((score + 1) / 5) * 100 : 0;

            clearClasses(bar);
            bar.classList.add(levelClasses[score] || levelClasses[0]);
            bar.style.width = `${width}%`;

            if (text) {
                text.textContent = value ? levelTexts[score] : 'Enter a password to check strength.';
            }
        }

        function reset(barEl, textEl) {
            const bar = getElement(barEl);
            const text = getElement(textEl);
            if (bar) {
                clearClasses(bar);
                bar.style.width = '0%';
            }
            if (text) {
                text.textContent = 'Enter a password to check strength.';
            }
        }

        return { update, reset };
    })();
</script>

<!-- Dashboard.js (load globally so it's available for AJAX-loaded dashboard content) -->
<script src="<?= base_url('assets/js/dashboard.js') ?>"></script>

<!-- Reports.js (load globally so it's available for AJAX-loaded reports content) -->
<script src="<?= base_url('assets/js/reports.js') ?>"></script>

<!-- Logs.js (load globally so it's available for AJAX-loaded logs content) -->
<script src="<?= base_url('assets/js/logs.js') ?>"></script>

<!-- Main JavaScript -->
<script>
    
    // Also set window.APP_BASE_URL for compatibility
    window.APP_BASE_URL = BASE_URL;

    // Layout stabilization to prevent jumping when scrollbars appear
    (function() {
        'use strict';
        
        // Function to stabilize layout
        function stabilizeLayout() {
            const htmlElement = document.documentElement;
            const bodyElement = document.body;
            
            // Force scrollbar gutter for stable layout
            if (htmlElement.style.scrollbarGutter !== 'stable') {
                htmlElement.style.scrollbarGutter = 'stable';
            }
            
            // Ensure overflow is scroll to prevent layout shift
            if (htmlElement.style.overflowY !== 'scroll') {
                htmlElement.style.overflowY = 'scroll';
            }
            
            // Prevent horizontal overflow
            if (bodyElement.style.overflowX !== 'hidden') {
                bodyElement.style.overflowX = 'hidden';
            }
            
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                const isMobileOrTablet = window.innerWidth <= 992;
                
                if (isMobileOrTablet) {
                    mainContent.style.width = '100%';
                    mainContent.style.marginLeft = '0';
                } else {
                    const rootStyles = getComputedStyle(document.documentElement);
                    const defaultSidebarWidth = (rootStyles.getPropertyValue('--sidebar-width') || '200px').trim() || '200px';
                    const collapsedSidebarWidth = (rootStyles.getPropertyValue('--sidebar-collapsed-width') || '60px').trim() || '60px';
                    const isExpanded = mainContent.classList.contains('expanded');
                    const activeSidebarWidth = isExpanded ? collapsedSidebarWidth : defaultSidebarWidth;
                    mainContent.style.width = `calc(100vw - ${activeSidebarWidth})`;
                    mainContent.style.marginLeft = activeSidebarWidth;
                }
            }
            
            // Stabilize all containers
            const containers = document.querySelectorAll('.container-fluid');
            containers.forEach(container => {
                if (container.style.width !== '100%') {
                    container.style.width = '100%';
                    container.style.maxWidth = 'none';
                    container.style.boxSizing = 'border-box';
                }
            });
            
            // Stabilize all cards
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                if (card.style.boxSizing !== 'border-box') {
                    card.style.boxSizing = 'border-box';
                }
            });
        }
        
        // Run stabilization immediately
        stabilizeLayout();
        
        // Run stabilization on DOM content loaded
        document.addEventListener('DOMContentLoaded', stabilizeLayout);
        
        // Run stabilization on window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(stabilizeLayout, 100);
        });
        
        // Run stabilization after AJAX content loads
        const observer = new MutationObserver(function(mutations) {
            let shouldStabilize = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    shouldStabilize = true;
                }
            });
            if (shouldStabilize) {
                setTimeout(stabilizeLayout, 50);
            }
        });
        
        // Start observing the content area for changes
        const contentArea = document.getElementById('contentArea');
        if (contentArea) {
            observer.observe(contentArea, {
                childList: true,
                subtree: true
            });
        }
        
        // Make stabilizeLayout globally available for manual calls
        window.stabilizeLayout = stabilizeLayout;
    })();

    // Modal scroll position reset functionality
    (function() {
        'use strict';
        
        // Function to reset modal scroll position
        function resetModalScroll(modalElement) {
            if (!modalElement) return;
            
            // Find the modal body and reset scroll (for Bootstrap modals)
            const modalBody = modalElement.querySelector('.modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
            
            // Also reset any scrollable containers within the modal
            const scrollableElements = modalElement.querySelectorAll('[style*="overflow"], .overflow-auto, .overflow-y-auto');
            scrollableElements.forEach(element => {
                if (element.scrollHeight > element.clientHeight) {
                    element.scrollTop = 0;
                }
            });
            
            // Special handling for Layout Designer modal
            if (modalElement.id === 'parkingLayoutDesignerModal') {
                // Reset the sidebar scroll position
                const sidebar = modalElement.querySelector('.parking-designer-sidebar');
                if (sidebar) {
                    sidebar.scrollTop = 0;
                }
                
                // Also reset any scrollable sections within the sidebar
                const scrollableSections = modalElement.querySelectorAll('.area-floor-section, .tools-section, .sections-section, .grid-size-section');
                scrollableSections.forEach(section => {
                    if (section.scrollHeight > section.clientHeight) {
                        section.scrollTop = 0;
                    }
                });
            }
        }
        
        // Function to handle modal show event
        function handleModalShow(event) {
            const modalElement = event.target;
            resetModalScroll(modalElement);
        }
        
        // Function to handle manual modal opening (for non-Bootstrap modals)
        function handleManualModalOpen() {
            // Check if this is a modal being shown manually
            if (this.classList && this.classList.contains('modal')) {
                if (this.style.display === 'block' || this.classList.contains('show')) {
                    resetModalScroll(this);
                }
            }
            
            // Special handling for Layout Designer modal
            if (this.id === 'parkingLayoutDesignerModal') {
                if (this.style.display !== 'none' && this.style.display !== '') {
                    resetModalScroll(this);
                }
            }
        }
        
        // Initialize modal scroll reset for all modals
        function initializeModalScrollReset() {
            // Get all modal elements
            const modals = document.querySelectorAll('.modal');
            
            // Remove existing event listeners to prevent duplicates
            modals.forEach(modal => {
                modal.removeEventListener('show.bs.modal', handleModalShow);
                modal.removeEventListener('shown.bs.modal', handleManualModalOpen);
                
                // Add new event listeners
                modal.addEventListener('show.bs.modal', handleModalShow);
                modal.addEventListener('shown.bs.modal', handleManualModalOpen);
                
                // Also handle style changes (for manual modal opening)
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                            // For Bootstrap modals
                            if (modal.style.display === 'block' || modal.classList.contains('show')) {
                                resetModalScroll(modal);
                            }
                            
                            // Special handling for Layout Designer modal
                            if (modal.id === 'parkingLayoutDesignerModal') {
                                if (modal.style.display !== 'none' && modal.style.display !== '') {
                                    resetModalScroll(modal);
                                }
                            }
                        } else if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            if (modal.classList.contains('show')) {
                                resetModalScroll(modal);
                            }
                        }
                    });
                });
                
                observer.observe(modal, {
                    attributes: true,
                    attributeFilter: ['style', 'class']
                });
            });
            
            // Special handling for Layout Designer modal (doesn't have .modal class)
            const layoutDesignerModal = document.getElementById('parkingLayoutDesignerModal');
            if (layoutDesignerModal) {
                // Remove existing listeners
                layoutDesignerModal.removeEventListener('shown.bs.modal', handleManualModalOpen);
                
                // Add new listener
                layoutDesignerModal.addEventListener('shown.bs.modal', handleManualModalOpen);
                
                // Create specific observer for Layout Designer modal
                const layoutObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                            if (layoutDesignerModal.style.display !== 'none' && layoutDesignerModal.style.display !== '') {
                                resetModalScroll(layoutDesignerModal);
                            }
                        }
                    });
                });
                
                layoutObserver.observe(layoutDesignerModal, {
                    attributes: true,
                    attributeFilter: ['style']
                });
            }
            
            console.log(`✅ Modal scroll reset initialized for ${modals.length} modals` + (layoutDesignerModal ? ' + Layout Designer modal' : ''));
        }
        
        // Initialize immediately
        initializeModalScrollReset();
        
        // Also handle jQuery modal events (if jQuery is available)
        if (typeof $ !== 'undefined') {
            $(document).on('show.bs.modal', '.modal', function() {
                resetModalScroll(this);
            });
            
            $(document).on('shown.bs.modal', '.modal', function() {
                resetModalScroll(this);
            });
        }
        
        // Re-initialize when DOM changes (for dynamically loaded modals)
        const observer = new MutationObserver(function(mutations) {
            let shouldReinitialize = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    // Check if any new modals were added
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.classList && node.classList.contains('modal')) {
                                shouldReinitialize = true;
                            } else if (node.querySelector) {
                                const modalInNode = node.querySelector('.modal');
                                if (modalInNode) {
                                    shouldReinitialize = true;
                                }
                            }
                        }
                    });
                }
            });
            
            if (shouldReinitialize) {
                setTimeout(initializeModalScrollReset, 100);
            }
        });
        
        // Start observing the document for modal changes
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Make function globally available for manual calls
        window.resetModalScroll = resetModalScroll;
        window.initializeModalScrollReset = initializeModalScrollReset;
        
        // Override the openLayoutDesigner function to include scroll reset
        if (typeof window.openLayoutDesigner === 'function') {
            const originalOpenLayoutDesigner = window.openLayoutDesigner;
            window.openLayoutDesigner = async function(...args) {
                // Call the original function first
                const result = await originalOpenLayoutDesigner.apply(this, args);
                
                // Reset scroll position after modal opens
                setTimeout(() => {
                    const modal = document.getElementById('parkingLayoutDesignerModal');
                    if (modal) {
                        resetModalScroll(modal);
                    }
                }, 100);
                
                return result;
            };
        }
    })();

    window.escapeHtml = function(text) {
        if (text === null || text === undefined) return '';
        const str = String(text);
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return str.replace(/[&<>"']/g, function(m) { return map[m]; });
    };

    window.sanitizeHtmlContent = function(html, options = {}) {
        if (html === null || html === undefined) return '';
        if (typeof html !== 'string') {
            html = String(html);
        }

        const { allowSvg = true } = options;
        const disallowedTags = allowSvg
            ? ['script', 'iframe', 'object', 'embed', 'link']
            : ['script', 'iframe', 'object', 'embed', 'link', 'svg'];

        const template = document.createElement('template');
        template.innerHTML = html;

        const sanitizeNode = (node) => {
            if (!node || !node.childNodes) return;
            const childNodes = Array.from(node.childNodes);

            childNodes.forEach(child => {
                if (child.nodeType === Node.ELEMENT_NODE) {
                    const tagName = child.tagName.toLowerCase();
                    if (disallowedTags.includes(tagName)) {
                        child.remove();
                        return;
                    }

                    Array.from(child.attributes || []).forEach(attr => {
                        const attrName = attr.name.toLowerCase();
                        const attrValue = (attr.value || '').trim();

                        if (attrName.startsWith('on')) {
                            child.removeAttribute(attr.name);
                            return;
                        }

                        if (['href', 'xlink:href', 'src', 'formaction'].includes(attrName) && attrValue.toLowerCase().startsWith('javascript:')) {
                            child.removeAttribute(attr.name);
                        }
                    });

                    sanitizeNode(child);
                } else if (child.nodeType === Node.COMMENT_NODE || child.nodeType === Node.DOCUMENT_TYPE_NODE) {
                    child.remove();
                }
            });
        };

        sanitizeNode(template.content);
        return template.innerHTML;
    };

    window.safeSetHtml = function(target, html, options) {
        if (!target) return;
        const sanitized = window.sanitizeHtmlContent ? window.sanitizeHtmlContent(html, options) : html;

        if (target instanceof Element || target instanceof DocumentFragment) {
            target.innerHTML = sanitized;
        } else if (target.jquery) {
            target.html(sanitized);
        }
    };

    (function patchJQueryDomMethods() {
        if (typeof window.jQuery === 'undefined') return;

        const $ = window.jQuery;

        function shouldBypassSanitization($elements) {
            if (!$elements || !$elements.length) return false;
            return Array.from($elements).every(el => {
                if (!(el instanceof Element)) return false;
                return Boolean(el.closest('[data-allow-unsafe-html="true"]'));
            });
        }

        function shouldAllowSvg($elements) {
            if (!$elements || !$elements.length) return true;
            return Array.from($elements).every(el => {
                if (!(el instanceof Element)) return true;
                return Boolean(el.closest('[data-allow-svg="false"]')) ? false : true;
            });
        }

        function sanitizeValue(value, $context) {
            if (value === null || value === undefined) return value;
            if (typeof value !== 'string') return value;
            if (!window.sanitizeHtmlContent) return value;

            if (shouldBypassSanitization($context)) {
                return value;
            }

            const allowSvg = shouldAllowSvg($context);
            return window.sanitizeHtmlContent(value, { allowSvg });
        }

        function overrideDomMutator(methodName) {
            if (typeof $.fn[methodName] !== 'function') return;
            const original = $.fn[methodName];

            $.fn[methodName] = function(...args) {
                if (!args.length) {
                    return original.apply(this, args);
                }

                const value = args[0];

                if (typeof value === 'function') {
                    const wrappedFn = function(index, oldHtml) {
                        const result = value.call(this, index, oldHtml);
                        if (typeof result === 'string') {
                            return sanitizeValue(result, $(this));
                        }
                        return result;
                    };

                    const newArgs = [...args];
                    newArgs[0] = wrappedFn;
                    return original.apply(this, newArgs);
                }

                if (typeof value === 'string') {
                    const newArgs = [...args];
                    newArgs[0] = sanitizeValue(value, this);
                    return original.apply(this, newArgs);
                }

                return original.apply(this, args);
            };
        }

        ['html', 'append', 'prepend', 'before', 'after', 'replaceWith'].forEach(overrideDomMutator);
    })();

    function getCookieValue(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    function getCsrfToken() {
        const cookieToken = getCookieValue('csrf_cookie_name');
        if (cookieToken) {
            return cookieToken;
        }
        const input = document.querySelector('input[name="csrf_test_name"]');
        if (input && input.value) {
            return input.value;
        }
        return null;
    }

    $(document).ready(function() {
        function scheduleLayoutStabilization() {
            window.requestAnimationFrame(() => window.stabilizeLayout?.());
        }
        $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
            const method = (options.type || options.method || 'GET').toUpperCase();
            if (method === 'GET' || method === 'HEAD' || method === 'OPTIONS') {
                return;
            }

            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                return;
            }

            const contentType = (options.contentType || '').toLowerCase();
            const isJson = contentType.includes('application/json');

            jqXHR.setRequestHeader('X-CSRF-TOKEN', csrfToken);

            if (isJson) {
                return;
            }

            if (typeof options.data === 'string') {
                if (!options.data.includes('csrf_test_name=')) {
                    options.data += (options.data.length ? '&' : '') + 'csrf_test_name=' + encodeURIComponent(csrfToken);
                }
            } else if (typeof options.data === 'object' && options.data !== null) {
                if (options.data.csrf_test_name === undefined) {
                    options.data.csrf_test_name = csrfToken;
                }
            } else if (options.data === undefined || options.data === null) {
                options.data = { csrf_test_name: csrfToken };
            }
        });

        // ====================================
        // SIDEBAR FUNCTIONALITY
        // ====================================
        
        // Sidebar Toggle
        $('#toggleSidebar').on('click', function() {
            const isMobileOrTablet = window.innerWidth <= 992;
            
            if (isMobileOrTablet) {
                // Mobile/Tablet: Toggle show class and overlay
                const $sidebar = $('#sidebar');
                const $overlay = $('#sidebarOverlay');
                const isShowing = $sidebar.hasClass('show');
                
                if (isShowing) {
                    // Close sidebar
                    $sidebar.removeClass('show');
                    $overlay.removeClass('show');
                    $('body').css('overflow', '');
                } else {
                    // Open sidebar
                    $sidebar.addClass('show');
                    $overlay.addClass('show');
                    $('body').css('overflow', 'hidden');
                    
                    // Restore active state and open submenus when sidebar reopens
                    if (typeof restoreActiveStateOnSidebarOpen === 'function') {
                        restoreActiveStateOnSidebarOpen();
                    }
                }

                scheduleLayoutStabilization();
            } else {
                // Desktop: Toggle collapsed class
                $('#sidebar').toggleClass('collapsed');
                $('#mainContent').toggleClass('expanded');
                
                // Save state to localStorage
                const isCollapsed = $('#sidebar').hasClass('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);

                scheduleLayoutStabilization();
            }
        });
        
        // Close sidebar when overlay is clicked (mobile/tablet)
        $('#sidebarOverlay').on('click', function() {
            $('#sidebar').removeClass('show');
            $(this).removeClass('show');
            $('body').css('overflow', '');
        });
        
        // Handle window resize
        $(window).on('resize', function() {
            const isMobileOrTablet = window.innerWidth <= 992;
            const $sidebar = $('#sidebar');
            const $overlay = $('#sidebarOverlay');
            
            if (isMobileOrTablet) {
                // On mobile/tablet, remove collapsed class and reset
                $sidebar.removeClass('collapsed');
                $('#mainContent').removeClass('expanded');
            } else {
                // On desktop, remove show class and overlay
                $sidebar.removeClass('show');
                $overlay.removeClass('show');
                $('body').css('overflow', '');
                
                // Restore collapsed state if saved
                const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (sidebarCollapsed) {
                    $sidebar.addClass('collapsed');
                    $('#mainContent').addClass('expanded');
                }
            }

            scheduleLayoutStabilization();
        });
        
        // Restore sidebar state from localStorage (desktop only)
        const isInitialMobileOrTablet = window.innerWidth <= 992;
        if (!isInitialMobileOrTablet) {
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                $('#sidebar').addClass('collapsed');
                $('#mainContent').addClass('expanded');
                scheduleLayoutStabilization();
            }
        } else {
            // On mobile/tablet, ensure sidebar is in drawer mode (not collapsed/expanded)
            $('#sidebar').removeClass('collapsed');
            $('#mainContent').removeClass('expanded');
            scheduleLayoutStabilization();
        }
        
        // ====================================
        // SUBMENU DROPDOWN TOGGLE
        // ====================================
        
        // Submenu Toggle (visual + navigation)
        $('.menu-link[data-submenu]').on('click', function(e) {
            e.preventDefault();
            
            const $parentMenu = $(this);
            const $sidebar = $('#sidebar');
            const isCollapsed = $sidebar.hasClass('collapsed');
            
            // If sidebar is collapsed, load the page directly instead of toggling submenu
            if (isCollapsed) {
                const route = $parentMenu.data('route');
                const title = $parentMenu.data('title');
                if (route) {
                    // Remove all active states
                    $('.menu-link, .submenu-link').removeClass('active');
                    
                    // Set parent as active
                    $parentMenu.addClass('active');
                    
                    // Find and highlight the matching submenu item
                    const submenuId = $parentMenu.data('submenu');
                    const $submenu = $('#' + submenuId);
                    const $matchingSubmenuItem = $submenu.find('.submenu-link[data-route="' + route + '"]');
                    if ($matchingSubmenuItem.length > 0) {
                        $matchingSubmenuItem.addClass('active');
                    }
                    
                    loadPage(route, title);
                }
                return;
            }
            
            // Otherwise, toggle submenu as normal
            const submenuId = $parentMenu.data('submenu');
            const $submenu = $('#' + submenuId);
            const isSubmenuOpen = $submenu.hasClass('show');
            
            // Toggle current submenu
            $submenu.toggleClass('show');
            $parentMenu.toggleClass('collapsed');
            
            // If opening the submenu (was closed), navigate to parent's default route and highlight it
            if (!isSubmenuOpen) {
                const route = $parentMenu.data('route');
                const title = $parentMenu.data('title');
                
                if (route && title) {
                    // Find the matching submenu item BEFORE removing active states
                    // Example: "User Management" has data-route="users", so find submenu item with data-route="users"
                    const $matchingSubmenuItem = $submenu.find('.submenu-link[data-route="' + route + '"]');
                    
                    // Remove all active states
                    $('.menu-link, .submenu-link').removeClass('active');
                    
                    // Set parent menu as active
                    $parentMenu.addClass('active');
                    
                    // Highlight the matching submenu item if found
                    if ($matchingSubmenuItem.length > 0) {
                        $matchingSubmenuItem.addClass('active');
                    } else {
                        // Fallback: If no exact match, highlight the first submenu item
                        const $firstSubmenuItem = $submenu.find('.submenu-link').first();
                        if ($firstSubmenuItem.length > 0) {
                            $firstSubmenuItem.addClass('active');
                            // Use the first submenu item's route and title if parent's route doesn't match
                            const firstRoute = $firstSubmenuItem.data('route');
                            const firstTitle = $firstSubmenuItem.data('title');
                            if (firstRoute && firstTitle) {
                                loadPage(firstRoute, firstTitle);
                                return; // Don't continue with parent's route
                            }
                        }
                    }
                    
                    // Load the parent's default page
                    loadPage(route, title);
                    
                    // Ensure active state persists after loadPage (in case restoreActiveState interferes)
                    setTimeout(function() {
                        $parentMenu.addClass('active');
                        if ($matchingSubmenuItem.length > 0) {
                            $matchingSubmenuItem.addClass('active');
                        }
                    }, 150); // Run after restoreActiveState (which runs at 100ms)
                }
            } else {
                // If closing submenu, check if any child is active
                // If so, keep parent highlighted
                const hasActiveChild = $submenu.find('.submenu-link.active').length > 0;
                if (hasActiveChild) {
                    $parentMenu.addClass('active');
                } else {
                    // Remove active from parent if no active child
                    $parentMenu.removeClass('active');
                }
            }
        });
        
        // Close sidebar on mobile when menu link is clicked (non-submenu items)
        $(document).on('click', '.menu-link:not([data-submenu])', function(e) {
            e.preventDefault();
            const isMobile = window.innerWidth <= 768;
            if (isMobile && $('#sidebar').hasClass('show')) {
                $('#sidebar').removeClass('show');
                $('#sidebarOverlay').removeClass('show');
                $('body').css('overflow', '');
            }
        });
        
        // Close sidebar on mobile when submenu link is clicked
        $(document).on('click', '.submenu-link', function(e) {
            e.preventDefault();
            const isMobile = window.innerWidth <= 768;
            if (isMobile && $('#sidebar').hasClass('show')) {
                $('#sidebar').removeClass('show');
                $('#sidebarOverlay').removeClass('show');
                $('body').css('overflow', '');
            }
        });
        
        // ====================================
        // THEME TOGGLE
        // ====================================
        
        // Dynamic Logo Switching Function
        window.updateLogoForTheme = function(theme) {
            const logo = document.getElementById('sidebarLogo');
            if (!logo) return;
            
            const baseUrl = window.APP_BASE_URL || '';
            let newLogoSrc;
            
            if (theme === 'dark') {
                // Use high-quality logo for dark mode
                newLogoSrc = baseUrl + 'assets/images/LOGOTAPPARKMINI_DARK.png';
            } else {
                // Use logo optimized for light mode
                newLogoSrc = baseUrl + 'assets/images/LOGOTAPPARKMINI_LIGHT.png';
            }
            
            // Test if the new logo exists, if not, fall back to original
            const tempImg = new Image();
            tempImg.onload = function() {
                logo.src = newLogoSrc;
            };
            tempImg.onerror = function() {
                // Fallback to original logo with CSS filter for theme
                logo.src = baseUrl + 'assets/images/LOGOTAPPARKMINI.png';
                if (theme === 'light') {
                    // Apply filter to make logo enhanced maroon for light mode
                    logo.style.filter = 'brightness(1.0) saturate(1.5) hue-rotate(-20deg)';
                } else {
                    // Apply filter to make logo enhanced maroon for dark mode
                    logo.style.filter = 'brightness(1.0) saturate(1.5) hue-rotate(-20deg)';
                }
            };
            tempImg.src = newLogoSrc;
        };
        
        // Theme Toggle (Top Navbar)
        $('#themeToggle').on('click', function() {
            const $html = $('html');
            const currentTheme = $html.attr('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            $html.attr('data-bs-theme', newTheme);
            
            // Update logo when theme changes
            window.updateLogoForTheme?.(newTheme);
            window.applyChartThemeDefaults?.();
            window.refreshExistingChartsTheme?.();
            
            // Update icon
            const icon = newTheme === 'dark' ? 'fa-sun' : 'fa-moon';
            $(this).find('i').removeClass('fa-sun fa-moon').addClass(icon);
            
            // Save to localStorage
            localStorage.setItem('theme', newTheme);
        });
        
        // Restore theme from localStorage
        const savedTheme = localStorage.getItem('theme') || 'light';
        $('html').attr('data-bs-theme', savedTheme);
        
        // Update logo on page load based on saved theme
        window.updateLogoForTheme?.(savedTheme);
        window.applyChartThemeDefaults?.();
        window.refreshExistingChartsTheme?.();
        
        const themeIcon = savedTheme === 'dark' ? 'fa-sun' : 'fa-moon';
        $('#themeToggle i').removeClass('fa-sun fa-moon').addClass(themeIcon);
        
        // ====================================
        // DYNAMIC PAGE LOADING (AJAX)
        // ====================================
        
        /**
         * Load page content via AJAX
         * Similar to practicesan pattern, but using data-route instead of text
         * 
         * @param {string} route - The route from data-route attribute (e.g., 'dashboard', 'parking/areas')
         * @param {string} title - The page title from data-title attribute
         */
        function loadPage(route, title) {
            // Build URL - CI4 will handle routing automatically
            const url = BASE_URL + route;
            
            // Update page title in header
            $('#pageTitle').text(title);
            
            // Show loading state
            $('#contentArea').html(`
                <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">diri para mo loading</span>
                        </div>
                        <h5 class="mb-2">Loading ${title}...</h5>
                        <p class="text-muted">Please wait while we load your data</p>
                    </div>
                </div>
            `);
            
            // Load content via AJAX (like practicesan)
            $.ajax({
                url: url,
                type: 'GET',
                timeout: 10000, // 10 second timeout
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'  // Tell CI4 this is AJAX request
                },
                success: function(html) {
                    // Clear old page data before loading new content
                    // This prevents conflicts when navigating between pages
                    if (typeof window.dashboardData !== 'undefined') {
                        delete window.dashboardData;
                    }
                    if (typeof window.reportsData !== 'undefined') {
                        delete window.reportsData;
                    }
                    
                    // Destroy any existing charts
                    if (typeof window.resetDashboardInitialization === 'function') {
                        window.resetDashboardInitialization();
                    }
                    
                    if (typeof window.dashboardCharts !== 'undefined') {
                        Object.keys(window.dashboardCharts).forEach(key => {
                            if (window.dashboardCharts[key]) {
                                try {
                                    window.dashboardCharts[key].destroy();
                                } catch (e) {
                                    // Ignore errors
                                }
                                window.dashboardCharts[key] = null;
                            }
                        });
                    }
                    if (typeof window.reportsCharts !== 'undefined') {
                        Object.keys(window.reportsCharts).forEach(key => {
                            if (window.reportsCharts[key]) {
                                try {
                                    window.reportsCharts[key].destroy();
                                } catch (e) {
                                    // Ignore errors
                                }
                                window.reportsCharts[key] = null;
                            }
                        });
                    }
                    if (typeof window.logsCharts !== 'undefined') {
                        Object.keys(window.logsCharts).forEach(key => {
                            if (window.logsCharts[key]) {
                                try {
                                    window.logsCharts[key].destroy();
                                } catch (e) {
                                    // Ignore errors
                                }
                                window.logsCharts[key] = null;
                            }
                        });
                    }
                    
                    // Fade in new content
                    $('#contentArea').hide().html(html).fadeIn(300);
                    
                    // Initialize page-specific scripts (like dashboard charts)
                    // Wait a bit for scripts in loaded HTML to execute first
                    // The content.php files have setTimeout to set window.dashboardData/reportsData
                    // So we need to wait for those to complete
                    setTimeout(function() {
                        if (typeof window.initPageScripts === 'function') {
                            window.initPageScripts();
                        }
                        
                        // Stabilize layout after content loads to prevent jumping
                        if (typeof window.stabilizeLayout === 'function') {
                            window.stabilizeLayout();
                        }
                    }, 300); // Wait for HTML scripts and DOM ready
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Failed to load page';
                    if (status === 'timeout') {
                        errorMessage = 'Request timed out. Please try again.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Page not found.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please refresh the page.';
                    }
                    
                    console.error('❌ Error loading page:', error);
                    $('#contentArea').html(`
                        <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
                            <div class="text-center">
                                <div class="text-danger mb-3">
                                    <i class="fas fa-exclamation-triangle fa-3x"></i>
                                </div>
                                <h5 class="text-danger mb-2">Error Loading ${title}</h5>
                                <p class="text-muted mb-3">${errorMessage}</p>
                                <button class="btn btn-primary" onclick="location.reload()">
                                    <i class="fas fa-refresh me-2"></i>Refresh Page
                                </button>
                            </div>
                        </div>
                    `);
                }
            });
        }
        
        // Make loadPage available globally (for use in other scripts)
        window.loadPage = loadPage;
        
        // ====================================
        // MENU CLICK HANDLERS
        // ====================================
        
        // Handle menu link clicks (items WITHOUT submenu) - Similar to practicesan pattern
        $('.menu-link:not([data-submenu])').on('click', function(e) {
            e.preventDefault();
            
            // Get route and title from data attributes (instead of text like practicesan)
            const route = $(this).data('route');  // e.g., "attendants", "logs"
            const title = $(this).data('title');   // e.g., "Attendant Management"
            
            if (!route) return;
            
            // Update active states
            $('.menu-link, .submenu-link').removeClass('active');
            $(this).addClass('active');
            
            // Load the page
            loadPage(route, title);
        });
        
        // Handle submenu link clicks - Similar to practicesan pattern
        $('.submenu-link').on('click', function(e) {
            e.preventDefault();
            
            // Get route and title from data attributes
            const route = $(this).data('route');  // e.g., "dashboard", "parking/areas"
            const title = $(this).data('title');   // e.g., "Dashboard", "Area & Section Management"
            
            if (!route) return;
            
            // Update active states
            $('.menu-link, .submenu-link').removeClass('active');
            $(this).addClass('active');
            
            // Also highlight parent menu and keep submenu open
            const $parentMenu = $(this).closest('.submenu').prev('.menu-link');
            $parentMenu.addClass('active');
            
            // Ensure the submenu is visible (important for when sidebar reopens)
            const submenuId = $parentMenu.data('submenu');
            if (submenuId) {
                $('#' + submenuId).addClass('show');
                $parentMenu.removeClass('collapsed');
            }
            
            // Load the page
            loadPage(route, title);
        });
        
        // ====================================
        // PAGE TITLE CLICK TO SCROLL TO TOP
        // ====================================
        
        // Make page title clickable to scroll to top
        $(document).on('click', '#pageTitle', function() {
            // Scroll to top with smooth animation
            $('html, body').animate({
                scrollTop: 0
            }, 300);
        });
        
        // ====================================
        // INITIAL PAGE LOAD
        // ====================================
        
        // Load Dashboard on startup (like practicesan loads home)
        loadPage('dashboard', 'Dashboard');
        
        // Set Dashboard as active by default
        $('.menu-link[data-route="dashboard"]').addClass('active');
        
        // ====================================
        // RESTORE ACTIVE STATE FUNCTIONS
        // ====================================
        
        /**
         * Restore active state when sidebar opens (mobile)
         * Ensures active menu items and their submenus are visible
         */
        function restoreActiveStateOnSidebarOpen() {
            // Find currently active submenu item
            const $activeSubmenuItem = $('.submenu-link.active');
            
            if ($activeSubmenuItem.length > 0) {
                // Get parent menu
                const $parentMenu = $activeSubmenuItem.closest('.submenu').prev('.menu-link');
                
                // Ensure parent is active
                $parentMenu.addClass('active');
                
                // Ensure submenu is open
                const submenuId = $parentMenu.data('submenu');
                if (submenuId) {
                    $('#' + submenuId).addClass('show');
                    $('#' + submenuId).addClass('active');
                    $parentMenu.removeClass('collapsed');
                }
            }
            
            // For menu items without submenu that are active
            const $activeMenuItem = $('.menu-link:not([data-submenu]).active');
            if ($activeMenuItem.length > 0) {
                // Just ensure it stays active (already handled)
            }
        }
        
        /**
         * Restore active state based on current page title
         * Used on initial page load
         */
        function restoreActiveState() {
            // Get current route from URL or page title
            const currentTitle = $('#pageTitle').text().trim();
            
            // Find matching menu item by title
            let $activeItem = null;
            
            // Check submenu links first
            $('.submenu-link').each(function() {
                if ($(this).data('title') === currentTitle) {
                    $activeItem = $(this);
                    return false; // break loop
                }
            });
            
            // If no submenu match, check menu links without submenu
            if (!$activeItem) {
                $('.menu-link:not([data-submenu])').each(function() {
                    if ($(this).data('title') === currentTitle) {
                        $activeItem = $(this);
                        return false; // break loop
                    }
                });
            }
            
            // If we found a match, set it as active
            if ($activeItem && $activeItem.length > 0) {
                // Remove all active states
                $('.menu-link, .submenu-link').removeClass('active');
                
                // Add active to the matched item
                $activeItem.addClass('active');
                
                // If it's a submenu item, also highlight parent and show submenu
                if ($activeItem.hasClass('submenu-link')) {
                    const $parentMenu = $activeItem.closest('.submenu').prev('.menu-link');
                    $parentMenu.addClass('active');
                    
                    // Keep submenu open
                    const submenuId = $parentMenu.data('submenu');
                    if (submenuId) {
                        $('#' + submenuId).addClass('show');
                        $parentMenu.removeClass('collapsed');
                    }
                }
            }
        }
        
        // Call restore function after a short delay to ensure page title is set
        setTimeout(restoreActiveState, 100);
        
        console.log('✅ TapPark v2 initialized - Navigation ready');
        
        // ====================================
        // SCROLL TO TOP BUTTON FUNCTIONALITY - OPTIMIZED
        // ====================================
        
        const scrollToTopBtn = document.getElementById('scrollToTopBtn');
        let ticking = false;
        let lastKnownScrollPosition = 0;
        let scrollThreshold = 300;
        let isVisible = false;
        
        // Throttled scroll handler for better performance
        function updateButtonVisibility() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const shouldShow = scrollTop > scrollThreshold;
            
            // Only update if state changed to avoid unnecessary DOM operations
            if (shouldShow !== isVisible) {
                if (shouldShow) {
                    // Show the button with smooth transition
                    scrollToTopBtn.classList.remove('hide');
                    scrollToTopBtn.classList.add('show');
                } else {
                    // Hide the button with smooth transition
                    scrollToTopBtn.classList.remove('show');
                    scrollToTopBtn.classList.add('hide');
                    
                    // Remove hide class after transition completes
                    setTimeout(() => {
                        if (scrollToTopBtn.classList.contains('hide')) {
                            scrollToTopBtn.classList.remove('hide');
                        }
                    }, 300);
                }
                isVisible = shouldShow;
            }
            
            ticking = false;
        }
        
        // Optimized scroll handler using requestAnimationFrame
        function handleScroll() {
            lastKnownScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
            
            if (!ticking) {
                requestAnimationFrame(updateButtonVisibility);
                ticking = true;
            }
        }
        
        // Enhanced smooth scroll to top with better easing
        function scrollToTop() {
            const startPosition = lastKnownScrollPosition;
            const startTime = performance.now();
            const duration = 600; // Reduced duration for snappier feel
            
            // Use a smoother easing function
            function easeOutCubic(t) {
                return 1 - Math.pow(1 - t, 3);
            }
            
            function animateScroll(currentTime) {
                const elapsedTime = currentTime - startTime;
                const progress = Math.min(elapsedTime / duration, 1);
                const easedProgress = easeOutCubic(progress);
                
                const currentPosition = startPosition * (1 - easedProgress);
                
                // Use smoother scrolling method
                window.scrollTo({
                    top: currentPosition,
                    behavior: 'instant'
                });
                
                if (progress < 1) {
                    requestAnimationFrame(animateScroll);
                } else {
                    // Ensure we're exactly at the top
                    window.scrollTo({
                        top: 0,
                        behavior: 'instant'
                    });
                    
                    // Trigger hide animation immediately when reaching top
                    setTimeout(() => {
                        lastKnownScrollPosition = 0;
                        updateButtonVisibility();
                    }, 100);
                }
            }
            
            requestAnimationFrame(animateScroll);
        }
        
        // Add optimized scroll event listener with passive option
        window.addEventListener('scroll', handleScroll, { 
            passive: true,
            capture: false 
        });
        
        // Enhanced click handler with better feedback
        scrollToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add visual feedback
            this.style.transform = 'translateY(-4px) scale(0.95)';
            
            // Scroll to top
            scrollToTop();
            
            // Reset visual feedback after a short delay
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
        
        // Improved keyboard support
        scrollToTopBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
        
        // Enhanced touch support with better feedback
        scrollToTopBtn.addEventListener('touchstart', function(e) {
            this.style.transform = 'translateY(-4px) scale(0.95)';
            e.preventDefault(); // Prevent double-tap zoom
        });
        
        scrollToTopBtn.addEventListener('touchend', function(e) {
            this.style.transform = '';
            this.click();
            e.preventDefault();
        });
        
        // Initialize on page load with delay to ensure DOM is ready
        requestAnimationFrame(() => {
            setTimeout(() => {
                updateButtonVisibility();
            }, 100);
        });
        
        // Optimized AJAX page load handling
        const originalLoadPage = window.loadPage;
        if (typeof originalLoadPage === 'function') {
            window.loadPage = function(route, title) {
                const result = originalLoadPage.call(this, route, title);
                
                // Re-check scroll position after content loads with RAF
                requestAnimationFrame(() => {
                    setTimeout(() => {
                        lastKnownScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
                        updateButtonVisibility();
                    }, 200);
                });
                
                return result;
            };
        }
        
        // Handle window resize for better responsive behavior
        window.addEventListener('resize', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    updateButtonVisibility();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
        
        console.log('✅ Scroll to top button initialized - Optimized version');
    });
    
    // ====================================
    // LOGOUT FUNCTIONALITY
    // ====================================
    /**
     * Show logout confirmation modal
     * Opens Bootstrap modal instead of browser alert
     * Prevents background scrolling when modal is open
     */
    function confirmLogout() {
        // Get or create Bootstrap modal instance
        const modalElement = document.getElementById('logoutConfirmModal');
        if (modalElement) {
            const logoutModal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false
            });
            logoutModal.show();
        } else {
            // Fallback to alert if modal not found
            console.warn('Logout modal not found, using alert fallback');
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = BASE_URL + 'logout';
            }
        }
    }
    
    // Prevent background scrolling for all modals (global handler)
    // Store scroll position and original body width to restore later
    let scrollPosition = 0;
    let originalBodyWidth = 0;
    let scrollbarWidth = 0;
    
    // Calculate scrollbar width once on page load
    function getScrollbarWidth() {
        // Create a temporary div to measure scrollbar width
        const outer = document.createElement('div');
        outer.style.visibility = 'hidden';
        outer.style.overflow = 'scroll'; // Force scrollbar
        outer.style.msOverflowStyle = 'scrollbar'; // Needed for old IE
        document.body.appendChild(outer);
        
        // Create inner div
        const inner = document.createElement('div');
        outer.appendChild(inner);
        
        // Calculate scrollbar width
        const scrollbarWidth = outer.offsetWidth - inner.offsetWidth;
        
        // Clean up
        outer.parentNode.removeChild(outer);
        
        return scrollbarWidth;
    }
    
    // Calculate scrollbar width on page load
    $(document).ready(function() {
        scrollbarWidth = getScrollbarWidth();
    });
    
    // Handle modal show for ALL modals including dynamically opened ones
    $(document).on('show.bs.modal', '.modal', function() {
        // Multi-modal z-index stacking fix
        const modalCount = $('.modal.show').length;
        if (modalCount > 0) {
            const baseZIndex = 1050 + (modalCount * 20);
            $(this).css('z-index', baseZIndex);
            
            // Push backdrop just below this modal
            setTimeout(() => {
                const $backdrop = $('.modal-backdrop').last();
                if ($backdrop.length) {
                    $backdrop.css('z-index', baseZIndex - 1);
                }
            }, 0);
        }

        // Store current scroll position
        scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        
        // Store the ACTUAL body/document width BEFORE modal opens (includes scrollbar space)
        // This is the full width of the document including scrollbar
        originalBodyWidth = document.documentElement.scrollWidth || document.body.scrollWidth || window.innerWidth;
        
        // Get current scrollbar width (in case it changed)
        if (scrollbarWidth === 0) {
            scrollbarWidth = window.innerWidth - document.documentElement.clientWidth || getScrollbarWidth();
        }
        
        // Prevent scrolling - use position fixed method
        // Set width to the original document width to prevent shrinkage
        $('body').addClass('modal-open');
        $('body').css({
            'overflow': 'hidden',
            'position': 'fixed',
            'width': originalBodyWidth + 'px',  // Use original width to prevent shrinkage
            'height': '100%',
            'top': '-' + scrollPosition + 'px',
            'left': '0',
            'right': '0',
            'padding-right': scrollbarWidth + 'px'  // Compensate for scrollbar to prevent horizontal shift
        });
        $('html').addClass('modal-open').css({
            'overflow': 'hidden',
            'height': '100%'
        });
        
        // Prevent scroll on content areas but keep layout intact
        $('.content-wrapper, .main-content').css({
            'overflow': 'hidden'
        });
        
        // Also handle #content div specifically (for AJAX-loaded content)
        $('#content').css({
            'overflow': 'hidden'
        });
    });
    
    // Also override Bootstrap's Modal.show() to ensure our fix is always applied
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const originalShow = bootstrap.Modal.prototype.show;
        bootstrap.Modal.prototype.show = function() {
            // Calculate scrollbar width if needed
            if (scrollbarWidth === 0) {
                scrollbarWidth = getScrollbarWidth();
            }
            
            // Store scroll position before showing
            scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
            originalBodyWidth = document.documentElement.scrollWidth || document.body.scrollWidth || window.innerWidth;
            
            // Call original show method
            originalShow.call(this);
            
            // Apply our fix immediately after modal is shown
            setTimeout(() => {
                if ($(this._element).hasClass('show')) {
                    $('body').css({
                        'overflow': 'hidden',
                        'position': 'fixed',
                        'width': originalBodyWidth + 'px',
                        'height': '100%',
                        'top': '-' + scrollPosition + 'px',
                        'left': '0',
                        'right': '0',
                        'padding-right': scrollbarWidth + 'px'
                    });
                    $('html').css({
                        'overflow': 'hidden',
                        'height': '100%'
                    });
                    $('#content').css({
                        'overflow': 'hidden'
                    });
                }
            }, 10);
        };
    }
    
    // Blur all focusable elements before modal hides to prevent aria-hidden warnings
    $(document).on('hide.bs.modal', '.modal', function() {
        const $modal = $(this);
        // Blur all focusable elements in this modal
        $modal.find('button, input, select, textarea, [tabindex]:not([tabindex="-1"])').each(function() {
            if (document.activeElement === this) {
                this.blur();
            }
        });
    });
    
    $(document).on('hidden.bs.modal', '.modal', function() {
        // Check if any other modals are still open
        if ($('.modal.show').length === 0) {
            // Restore scroll - remove all fixed positioning
            $('body').removeClass('modal-open');
            $('body').css({
                'overflow': '',
                'position': '',
                'width': '',
                'height': '',
                'top': '',
                'left': '',
                'right': '',
                'padding-right': ''
            });
            $('html').removeClass('modal-open').css({
                'overflow': '',
                'height': ''
            });
            
            // Restore content areas
            $('.content-wrapper, .main-content').css({
                'overflow': ''
            });
            
            // Restore scroll position (small delay to ensure DOM is ready)
            setTimeout(function() {
                window.scrollTo(0, scrollPosition);
            }, 10);
        }
    });
    
    // Handle logout confirmation button click
    $(document).on('click', '#confirmLogoutBtn', function() {
        // Show loading state on button
        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.prop('disabled', true);
        $btn.html('<span class="spinner-border spinner-border-sm me-1"></span>Logging out...');
        
        // Hide modal
        const modalElement = document.getElementById('logoutConfirmModal');
        if (modalElement) {
            const logoutModal = bootstrap.Modal.getInstance(modalElement);
            if (logoutModal) {
                logoutModal.hide();
            }
        }
        
        // Redirect to logout endpoint
        // The server will destroy session and redirect to login page
        window.location.href = BASE_URL + 'logout';
    });
    
    // Make confirmLogout available globally
    window.confirmLogout = confirmLogout;
    
    // ====================================
    // PROFILE MODAL FUNCTIONALITY
    // ====================================
    
    let profileModalInstance = null;
    function openProfileModal() {
        if (!profileModalInstance) {
            const modalElement = document.getElementById('profileModal');
            if (!modalElement) {
                console.error('Profile modal element not found');
                return;
            }
            profileModalInstance = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
        }
        profileModalInstance.show();
    }
    
    // Make openProfileModal available globally
    window.openProfileModal = openProfileModal;
    
    // Reset profile modal to view mode when opened
    $(document).on('show.bs.modal', '#profileModal', function() {
        // Reset to view mode
        $('#firstName, #lastName, #email').prop('readonly', true).addClass('bg-light');
        $('#profilePictureFile').prop('disabled', true).addClass('bg-light');
        $('#profilePictureInput').prop('disabled', true);
        $('#profilePictureCameraBtn').addClass('disabled').css('pointer-events', 'none');
        
        // Hide action buttons and confirmation, show edit button
        $('#profileNormalActions').addClass('d-none');
        $('#profileConfirmSection').addClass('d-none');
        $('#profileFormSection').removeClass('d-none');
        $('#profileEditBtn').removeClass('d-none');
        
        // Hide messages
        $('#profileError, #profileSuccess').addClass('d-none');
        
        // Remove validation classes
        $('#profileForm input').removeClass('is-invalid is-valid');
        
        // Reset file inputs
        $('#profilePictureFile, #profilePictureInput').val('');
    });
    
    // Dark Mode Toggle in Profile Modal Settings
    $(document).on('change', '#darkModeToggle', function() {
        if($(this).is(':checked')) {
            $('html').attr('data-bs-theme', 'dark');
            $('#themeToggle i').removeClass('fa-moon').addClass('fa-sun');
            localStorage.setItem('theme', 'dark');
            updateLogoForTheme('dark');
        } else {
            $('html').attr('data-bs-theme', 'light');
            $('#themeToggle i').removeClass('fa-sun').addClass('fa-moon');
            localStorage.setItem('theme', 'light');
            updateLogoForTheme('light');
        }
    });
    
    // Load saved theme preference when profile modal opens
    $(document).on('shown.bs.modal', '#profileModal', function() {
        const savedTheme = localStorage.getItem('theme');
        if(savedTheme === 'dark') {
            $('#darkModeToggle').prop('checked', true);
        } else {
            $('#darkModeToggle').prop('checked', false);
        }
    });
    
    window.profileCropState = window.profileCropState || {
        image: null,
        file: null,
        zoom: 1,
        offsetX: 0,
        offsetY: 0,
        dragging: false,
        lastX: 0,
        lastY: 0,
        modal: null
    };

    function drawProfileCropCanvas() {
        const state = window.profileCropState;
        const canvas = document.getElementById('profileCropCanvas');
        if (!canvas || !state.image) return;
        const ctx = canvas.getContext('2d');
        const size = canvas.width;

        ctx.clearRect(0, 0, size, size);

        const img = state.image;
        const baseScale = Math.max(size / img.width, size / img.height);
        const scale = baseScale * (state.zoom || 1);

        const drawW = img.width * scale;
        const drawH = img.height * scale;
        const x = (size - drawW) / 2 + (state.offsetX || 0);
        const y = (size - drawH) / 2 + (state.offsetY || 0);

        ctx.drawImage(img, x, y, drawW, drawH);
    }

    function openProfileCropModal(file) {
        const state = window.profileCropState;
        state.file = file;
        state.zoom = 1;
        state.offsetX = 0;
        state.offsetY = 0;

        const reader = new FileReader();
        reader.onload = function(ev) {
            const img = new Image();
            img.onload = function() {
                state.image = img;
                $('#profileCropZoom').val(1);
                drawProfileCropCanvas();
                if (!state.modal) {
                    state.modal = new bootstrap.Modal(document.getElementById('profileImageCropModal'));
                }
                state.modal.show();
            };
            img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    }

    $(document).on('change', '#profilePictureInput, #profilePictureFile', function(e) {
        if (window.isApplyingCroppedProfileImage) {
            return;
        }

        const file = e.target.files[0];
        if (!file) return;

        if ($('#profilePictureFile').prop('disabled') && $('#profilePictureInput').prop('disabled')) {
            $(this).val('');
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            $('#profileError').find('span').text('File size exceeds 2MB limit.');
            $('#profileError').removeClass('d-none');
            $(this).val('');
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            $('#profileError').find('span').text('Invalid file type. Only JPEG, PNG, and GIF images are allowed.');
            $('#profileError').removeClass('d-none');
            $(this).val('');
            return;
        }

        $('#profileError').addClass('d-none');
        openProfileCropModal(file);
    });

    $(document).on('input', '#profileCropZoom', function() {
        window.profileCropState.zoom = parseFloat($(this).val()) || 1;
        drawProfileCropCanvas();
    });

    $(document).on('mousedown', '#profileCropCanvas', function(ev) {
        const state = window.profileCropState;
        state.dragging = true;
        state.lastX = ev.clientX;
        state.lastY = ev.clientY;
    });

    $(document).on('mousemove', function(ev) {
        const state = window.profileCropState;
        if (!state.dragging) return;
        const dx = ev.clientX - state.lastX;
        const dy = ev.clientY - state.lastY;
        state.lastX = ev.clientX;
        state.lastY = ev.clientY;
        state.offsetX = (state.offsetX || 0) + dx;
        state.offsetY = (state.offsetY || 0) + dy;
        drawProfileCropCanvas();
    });

    $(document).on('mouseup', function() {
        window.profileCropState.dragging = false;
    });

    $(document).on('touchstart', '#profileCropCanvas', function(ev) {
        const t = ev.originalEvent.touches && ev.originalEvent.touches[0];
        if (!t) return;
        const state = window.profileCropState;
        state.dragging = true;
        state.lastX = t.clientX;
        state.lastY = t.clientY;
    });

    $(document).on('touchmove', '#profileCropCanvas', function(ev) {
        const t = ev.originalEvent.touches && ev.originalEvent.touches[0];
        if (!t) return;
        const state = window.profileCropState;
        if (!state.dragging) return;
        const dx = t.clientX - state.lastX;
        const dy = t.clientY - state.lastY;
        state.lastX = t.clientX;
        state.lastY = t.clientY;
        state.offsetX = (state.offsetX || 0) + dx;
        state.offsetY = (state.offsetY || 0) + dy;
        drawProfileCropCanvas();
        ev.preventDefault();
    });

    $(document).on('touchend touchcancel', function() {
        window.profileCropState.dragging = false;
    });

    $(document).on('click', '#profileCropApplyBtn', function() {
        const state = window.profileCropState;
        const canvas = document.getElementById('profileCropCanvas');
        if (!canvas || !state.image || !state.file) return;

        canvas.toBlob(function(blob) {
            if (!blob) return;
            const ext = (state.file.type || 'image/jpeg').split('/')[1] || 'jpg';
            const fileName = `profile_crop.${ext}`;
            const croppedFile = new File([blob], fileName, { type: state.file.type || 'image/jpeg' });

            const dt = new DataTransfer();
            dt.items.add(croppedFile);

            window.isApplyingCroppedProfileImage = true;
            $('#profilePictureInput')[0].files = dt.files;
            $('#profilePictureFile')[0].files = dt.files;
            window.isApplyingCroppedProfileImage = false;

            const previewUrl = URL.createObjectURL(croppedFile);
            $('#profileAvatarPreview').attr('src', previewUrl);
            $('#profileError').addClass('d-none');

            if (state.modal) {
                state.modal.hide();
            }
        }, window.profileCropState.file.type || 'image/jpeg', 0.92);
    });

    $(document).on('click', '#profileCropCancelBtn', function() {
        window.profileCropState.file = null;
        window.profileCropState.image = null;
        $('#profilePictureFile, #profilePictureInput').val('');
    });
    
    // Sync both file inputs
    $('#profilePictureInput').on('change', function() {
        if (this.files.length > 0) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(this.files[0]);
            $('#profilePictureFile')[0].files = dataTransfer.files;
        }
    });
    
    $('#profilePictureFile').on('change', function() {
        if (this.files.length > 0) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(this.files[0]);
            $('#profilePictureInput')[0].files = dataTransfer.files;
            // Update preview
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#profileAvatarPreview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        }
    });
    
    // Initialize original profile values on page load
    <?php
    // Fetch user data from database, not just session
    $userModel = new \App\Models\UserModel();
    $userId = session()->get('user_id');
    $userData = null;
    $profilePic = null;
    $firstName = '';
    $lastName = '';
    $email = '';
    
    if ($userId) {
        $userData = $userModel->find($userId);
        if ($userData) {
            // Get data from database first
            $firstName = $userData['first_name'] ?? session()->get('first_name') ?? '';
            $lastName = $userData['last_name'] ?? session()->get('last_name') ?? '';
            $email = $userData['email'] ?? session()->get('email') ?? '';
            
            if (!empty($userData['profile_picture'])) {
                $profilePic = $userData['profile_picture'];
            }
        }
    }
    
    // Fallback to session if database fetch failed
    if (empty($firstName)) {
        $firstName = session()->get('first_name') ?? '';
    }
    if (empty($lastName)) {
        $lastName = session()->get('last_name') ?? '';
    }
    if (empty($email)) {
        $email = session()->get('email') ?? '';
    }
    if (empty($profilePic)) {
        $profilePic = session()->get('profile_picture');
    }
    
    $firstLetter = strtoupper(substr($firstName, 0, 1));
    if (empty($firstLetter)) {
        $firstLetter = 'A';
    }
    $avatarSrc = !empty($profilePic) && file_exists(ROOTPATH . 'public/uploads/profiles/' . $profilePic)
        ? base_url('uploads/profiles/' . $profilePic)
        : 'data:image/svg+xml;base64,' . base64_encode('<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="#800000"/><text x="50%" y="50%" font-family="Arial, sans-serif" font-size="40" font-weight="bold" fill="#ffffff" text-anchor="middle" dominant-baseline="central">' . htmlspecialchars($firstLetter) . '</text></svg>');
    ?>
    window.originalProfileValues = {
        firstName: '<?= esc($firstName) ?>',
        lastName: '<?= esc($lastName) ?>',
        email: '<?= esc($email) ?>',
        profilePicture: '<?= esc($profilePic ?? '') ?>',
        avatarSrc: '<?= esc($avatarSrc) ?>'
    };

    const profileEmailValidator = window.isValidEmailStrict || function(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    };
    
    // Profile Edit Button - Enable Edit Mode
    $(document).on('click', '#profileEditBtn', function() {
        // Enable form fields
        $('#firstName, #lastName, #email').prop('readonly', false).removeClass('bg-light');
        $('#profilePictureFile').prop('disabled', false).removeClass('bg-light');
        $('#profilePictureInput').prop('disabled', false);
        $('#profilePictureCameraBtn').removeClass('disabled').css('pointer-events', 'auto');
        
        // Show action buttons, hide edit button
        $('#profileNormalActions').removeClass('d-none');
        $('#profileEditBtn').addClass('d-none');
        
        // Hide confirmation section if visible
        $('#profileConfirmSection').addClass('d-none');
        $('#profileFormSection').removeClass('d-none');
        
        // Hide messages
        $('#profileError, #profileSuccess').addClass('d-none');
        
        // Remove validation classes
        $('#profileForm input').removeClass('is-invalid is-valid');
    });
    
    // Profile Cancel Button - Exit Edit Mode and Revert Changes
    $(document).on('click', '#profileCancelBtn', function() {
        // Use stored original values (updated after successful save)
        const original = window.originalProfileValues || {};
        
        $('#firstName').val(original.firstName || '<?= esc($firstName) ?>');
        $('#lastName').val(original.lastName || '<?= esc($lastName) ?>');
        $('#email').val(original.email || '<?= esc($email) ?>');
        $('#profilePictureFile, #profilePictureInput').val('');
        
        // Reset avatar preview to original/saved
        const avatarSrc = original.avatarSrc || '<?= esc($avatarSrc) ?>';
        $('#profileAvatarPreview').attr('src', avatarSrc);
        
        // Disable form fields (return to view mode)
        $('#firstName, #lastName, #email').prop('readonly', true).addClass('bg-light');
        $('#profilePictureFile').prop('disabled', true).addClass('bg-light');
        $('#profilePictureInput').prop('disabled', true);
        $('#profilePictureCameraBtn').addClass('disabled').css('pointer-events', 'none');
        
        // Hide action buttons, show edit button
        $('#profileNormalActions').addClass('d-none');
        $('#profileEditBtn').removeClass('d-none');
        
        // Hide confirmation section
        $('#profileConfirmSection').addClass('d-none');
        $('#profileFormSection').removeClass('d-none');
        
        // Hide messages
        $('#profileError, #profileSuccess').addClass('d-none');
        
        // Remove validation classes
        $('#profileForm input').removeClass('is-invalid is-valid');
    });
    
    // Profile Save Changes Button - Show Confirmation
    $(document).on('click', '#profileSaveBtn', function(e) {
        e.preventDefault();
        
        // Hide previous messages
        $('#profileError, #profileSuccess').addClass('d-none');
        $('#profileForm input').removeClass('is-invalid is-valid');
        
        // Get form data
        const firstName = $('#firstName').val().trim();
        const lastName = $('#lastName').val().trim();
        const email = $('#email').val().trim();
        
        // Validation
        if (!firstName || firstName.length < 2) {
            $('#profileError').find('span').text('First name must be at least 2 characters long.');
            $('#profileError').removeClass('d-none');
            $('#firstName').addClass('is-invalid');
            return;
        }
        
        if (!lastName || lastName.length < 2) {
            $('#profileError').find('span').text('Last name must be at least 2 characters long.');
            $('#profileError').removeClass('d-none');
            $('#lastName').addClass('is-invalid');
            return;
        }
        
        if (!email || !profileEmailValidator(email)) {
            $('#profileError').find('span').text('Please enter a valid email address.');
            $('#profileError').removeClass('d-none');
            $('#email').addClass('is-invalid');
            return;
        }
        
        // Check if anything changed (use stored original values)
        const original = window.originalProfileValues || {};
        const originalFirstName = original.firstName || '<?= esc($firstName) ?>';
        const originalLastName = original.lastName || '<?= esc($lastName) ?>';
        const originalEmail = original.email || '<?= esc($email) ?>';
        const fileInput = $('#profilePictureFile')[0];
        const hasFile = fileInput.files && fileInput.files.length > 0;
        
        if (firstName === originalFirstName && 
            lastName === originalLastName && 
            email === originalEmail && 
            !hasFile) {
            $('#profileError').find('span').text('No changes detected.');
            $('#profileError').removeClass('d-none');
            return;
        }
        
        // Show confirmation section
        $('#profileConfirmMessage').text('Are you sure you want to save these changes?');
        const changes = [];
        if (firstName !== originalFirstName) changes.push(`First Name: "${originalFirstName}" → "${firstName}"`);
        if (lastName !== originalLastName) changes.push(`Last Name: "${originalLastName}" → "${lastName}"`);
        if (email !== originalEmail) changes.push(`Email: "${originalEmail}" → "${email}"`);
        if (hasFile) changes.push('Profile Picture: New file selected');
        
        const description = changes.length > 0 
            ? 'You are about to update: ' + changes.join(', ')
            : 'Please review your information before confirming.';
        $('#profileConfirmDescription').text(description);
        
        $('#profileNormalActions').addClass('d-none');
        $('#profileConfirmSection').removeClass('d-none');
        $('#profileFormSection').addClass('d-none');
    });
    
    // Profile Confirm Cancel Button (No) - Hide Confirmation
    $(document).on('click', '#profileConfirmCancelBtn', function() {
        $('#profileConfirmSection').addClass('d-none');
        $('#profileNormalActions').removeClass('d-none');
        $('#profileFormSection').removeClass('d-none');
        $('#profileConfirmYesBtn').prop('disabled', false);
    });
    
    // Profile Confirm Yes Button - Submit Form
    $(document).on('click', '#profileConfirmYesBtn', function() {
        // Get form data
        const firstName = $('#firstName').val().trim();
        const lastName = $('#lastName').val().trim();
        const email = $('#email').val().trim();
        const fileInput = $('#profilePictureFile')[0];
        const hasFile = fileInput.files && fileInput.files.length > 0;
        
        // Create FormData for file upload
        const formData = new FormData();
        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('email', email);
        
        if (hasFile) {
            formData.append('profile_picture', fileInput.files[0]);
        }
        
        // Show loading
        const confirmBtn = $('#profileConfirmYesBtn');
        const originalText = confirmBtn.html();
        confirmBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        
        // AJAX request to backend
        $.ajax({
            url: BASE_URL + 'profile/update',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#profileSuccess').find('span').text(response.message || 'Profile updated successfully!');
                    $('#profileSuccess').removeClass('d-none');
                    
                    // Update displayed name and email in modal
                    $('#profileDisplayName').text(firstName + ' ' + lastName);
                    $('#profileDisplayEmail').text(email);
                    
                    // Update sidebar user info (no reload needed - update directly via AJAX)
                    $('.user-name').text(firstName + ' ' + lastName);
                    $('.user-email').text(email);
                    
                    // Update avatars if new picture was uploaded
                    if (response.profile_picture) {
                        const newAvatarSrc = BASE_URL + 'uploads/profiles/' + response.profile_picture + '?t=' + new Date().getTime();
                        $('#profileAvatarPreview').attr('src', newAvatarSrc);
                        $('#sidebarUserAvatar').attr('src', newAvatarSrc);
                    }
                    
                    // Reset file inputs
                    $('#profilePictureFile, #profilePictureInput').val('');
                    
                    // Update the original values for cancel button (so cancel resets to new saved values)
                    const newAvatarSrc = response.profile_picture 
                        ? BASE_URL + 'uploads/profiles/' + response.profile_picture + '?t=' + new Date().getTime()
                        : window.originalProfileValues.avatarSrc;
                    
                    window.originalProfileValues = {
                        firstName: firstName,
                        lastName: lastName,
                        email: email,
                        profilePicture: response.profile_picture || window.originalProfileValues.profilePicture,
                        avatarSrc: newAvatarSrc
                    };
                    
                    // Return to view mode
                    $('#firstName, #lastName, #email').prop('readonly', true).addClass('bg-light');
                    $('#profilePictureFile').prop('disabled', true).addClass('bg-light');
                    $('#profilePictureInput').prop('disabled', true);
                    $('#profilePictureCameraBtn').addClass('disabled').css('pointer-events', 'none');
                    $('#profileNormalActions').addClass('d-none');
                    $('#profileConfirmSection').addClass('d-none');
                    $('#profileFormSection').removeClass('d-none');
                    $('#profileEditBtn').removeClass('d-none');
                } else {
                    // Show error message
                    let errorMsg = response.message || 'Failed to update profile';
                    
                    // If there are field-specific errors, show them
                    if (response.errors) {
                        const errorKeys = Object.keys(response.errors);
                        if (errorKeys.length > 0) {
                            errorMsg = response.errors[errorKeys[0]];
                            // Mark the specific field as invalid
                            const fieldMap = {
                                'first_name': '#firstName',
                                'last_name': '#lastName',
                                'email': '#email'
                            };
                            if (fieldMap[errorKeys[0]]) {
                                $(fieldMap[errorKeys[0]]).addClass('is-invalid');
                            }
                        }
                    }
                    
                    $('#profileError').find('span').text(errorMsg);
                    $('#profileError').removeClass('d-none');
                    
                    // Hide confirmation, show normal actions
                    $('#profileConfirmSection').addClass('d-none');
                    $('#profileNormalActions').removeClass('d-none');
                    $('#profileFormSection').removeClass('d-none');
                }
                
                // Re-enable button
                confirmBtn.prop('disabled', false).html(originalText);
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMsg = 'An error occurred. Please try again.';
                
                if (response && response.message) {
                    errorMsg = response.message;
                } else if (xhr.status === 401) {
                    errorMsg = 'Unauthorized. Please log in again.';
                } else if (xhr.status === 400 && response && response.errors) {
                    // Show first validation error
                    const errorKeys = Object.keys(response.errors);
                    if (errorKeys.length > 0) {
                        errorMsg = response.errors[errorKeys[0]];
                        const fieldMap = {
                            'first_name': '#firstName',
                            'last_name': '#lastName',
                            'email': '#email'
                        };
                        if (fieldMap[errorKeys[0]]) {
                            $(fieldMap[errorKeys[0]]).addClass('is-invalid');
                        }
                    }
                }
                
                $('#profileError').find('span').text(errorMsg);
                $('#profileError').removeClass('d-none');
                
                // Hide confirmation, show normal actions
                $('#profileConfirmSection').addClass('d-none');
                $('#profileNormalActions').removeClass('d-none');
                $('#profileFormSection').removeClass('d-none');
                
                // Re-enable button
                confirmBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Profile Form Submit Handler (prevent default form submission)
    $(document).on('submit', '#profileForm', function(e) {
        e.preventDefault();
        // Form submission is now handled through the Edit button flow
    });
    
    // ====================================
    // CHANGE PASSWORD FUNCTIONALITY
    // ====================================
    
    function openChangePasswordModal() {
        // Hide profile modal first
        const profileModal = bootstrap.Modal.getInstance(document.getElementById('profileModal'));
        if (profileModal) {
            profileModal.hide();
        }
        
        // Reset form
        $('#changePasswordForm')[0].reset();
        $('#changePasswordForm').removeClass('was-validated');
        $('#changePasswordError, #changePasswordSuccess').addClass('d-none');
        $('#passwordStrength').html('Password strength: <span class="text-muted">Not set</span>');
        
        // Wait a bit for profile modal to hide, then show change password modal
        setTimeout(function() {
            const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
            modal.show();
        }, 300);
    }
    window.openChangePasswordModal = openChangePasswordModal;
    
    // Function to go back to profile modal
    function backToProfileModal() {
        // Hide change password modal
        const changePasswordModal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
        if (changePasswordModal) {
            changePasswordModal.hide();
        }
        
        // Wait a bit for change password modal to hide, then show profile modal
        setTimeout(function() {
            const profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
            profileModal.show();
            
            // Switch to Settings tab and scroll to password section
            $('#settings-tab').tab('show');
            setTimeout(function() {
                const passwordSection = document.querySelector('#settings-content .settings-card:last-child');
                if (passwordSection) {
                    passwordSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 100);
        }, 300);
    }
    window.backToProfileModal = backToProfileModal;
    
    // Toggle Password Visibility
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = event.currentTarget;
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    window.togglePasswordVisibility = togglePasswordVisibility;
    
    // Password Strength Indicator
    $(document).on('input', '#newPassword', function() {
        const password = $(this).val();
        const strengthIndicator = $('#passwordStrength');
        
        if (password.length === 0) {
            strengthIndicator.html('Password strength: <span class="text-muted">Not set</span>');
            return;
        }
        
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        let text = '';
        let icon = '';
        let className = '';
        
        if (strength <= 2) {
            text = 'Weak';
            icon = '<i class="fas fa-times-circle me-1"></i>';
            className = 'text-danger';
        } else if (strength <= 3) {
            text = 'Medium';
            icon = '<i class="fas fa-exclamation-circle me-1"></i>';
            className = 'text-warning';
        } else {
            text = 'Strong';
            icon = '<i class="fas fa-check-circle me-1"></i>';
            className = 'text-success';
        }
        
        strengthIndicator.html(`Password strength: <span class="${className} fw-bold">${icon}${text}</span>`);
    });
    
    // Change Password Form Submission
    $(document).on('submit', '#changePasswordForm', function(e) {
        e.preventDefault();
        
        const currentPassword = $('#currentPassword').val();
        const newPassword = $('#newPassword').val();
        const confirmPassword = $('#confirmPassword').val();
        
        // Hide previous messages
        $('#changePasswordError, #changePasswordSuccess').addClass('d-none');
        $('#changePasswordForm input').removeClass('is-invalid');
        
        // Validation
        if (newPassword !== confirmPassword) {
            $('#changePasswordError').find('span').text('New passwords do not match!');
            $('#changePasswordError').removeClass('d-none');
            $('#confirmPassword').addClass('is-invalid');
            return;
        }
        
        if (newPassword.length < 8) {
            $('#changePasswordError').find('span').text('Password must be at least 8 characters long!');
            $('#changePasswordError').removeClass('d-none');
            $('#newPassword').addClass('is-invalid');
            return;
        }
        
        if (!/\d/.test(newPassword) || !/[a-zA-Z]/.test(newPassword)) {
            $('#changePasswordError').find('span').text('Password must contain both letters and numbers!');
            $('#changePasswordError').removeClass('d-none');
            $('#newPassword').addClass('is-invalid');
            return;
        }
        
        // Show loading
        const submitBtn = $('#changePasswordBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Changing...');
        
        // AJAX request to backend
        $.ajax({
            url: BASE_URL + 'profile/change-password',
            method: 'POST',
            data: {
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            },
            success: function(response) {
                if (response.success) {
                    $('#changePasswordSuccess').find('span').text(response.message || 'Password changed successfully!');
                    $('#changePasswordSuccess').removeClass('d-none');
                    $('#changePasswordForm')[0].reset();
                    $('#passwordStrength').html('Password strength: <span class="text-muted">Not set</span>');
                    
                    // Close modal and go back to profile modal after 2 seconds
                    setTimeout(function() {
                        const changePasswordModal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
                        if (changePasswordModal) {
                            changePasswordModal.hide();
                        }
                        // Go back to profile modal after a short delay
                        setTimeout(function() {
                            const profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
                            profileModal.show();
                            $('#settings-tab').tab('show');
                        }, 300);
                    }, 2000);
                } else {
                    $('#changePasswordError').find('span').text(response.message || 'Failed to change password');
                    $('#changePasswordError').removeClass('d-none');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                $('#changePasswordError').find('span').text(response?.message || 'An error occurred. Please try again.');
                $('#changePasswordError').removeClass('d-none');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // ====================================
    // SHARED FILTERS INITIALIZATION
    // ====================================
    /**
     * Initialize shared filters for a specific entity type
     * @param {string} entityType - The entity type (users, attendants, subscriptions, logs)
     */
    window.initSharedFilters = function(entityType) {
        const filterCard = $('#sharedFiltersCard');
        if (!filterCard.length) {
            return; // Shared filters not on this page
        }
        
        // Set entity type
        $('#filterEntityType').val(entityType);
        
        // Hide all filter fields first
        $('.filter-field').hide();
        
        // Reset button text and style first
        if ($('#sharedRefreshBtnText').length) {
            $('#sharedRefreshBtnText').text('Refresh');
        }
        if ($('#sharedRefreshBtn').length) {
            $('#sharedRefreshBtn').removeClass('btn-primary').addClass('btn-info');
        }
        
        // Show only relevant filters based on entity type
        switch(entityType) {
            case 'users':
                $('.filter-search').show();
                $('.filter-status').show();
                $('.filter-online-status').show();
                // Set search placeholder
                $('#sharedSearchInput').attr('placeholder', 'Search by ID, name, email...');
                // Show export button for users
                $('#sharedExportBtn').css('display', 'block');
                break;

            case 'attendants':
                $('.filter-search').show();
                $('.filter-user-type').show();
                $('.filter-status').show();
                $('.filter-online-status').show();
                // Set search placeholder
                $('#sharedSearchInput').attr('placeholder', 'Search by ID, name, email...');
                // Show export button for attendants
                $('#sharedExportBtn').css('display', 'block');
                break;
                
            case 'subscriptions':
                $('.filter-search').show();
                $('.filter-price-range').show();
                $('.filter-hours-range').show();
                $('.filter-plan-status').show();
                // Set search placeholder
                $('#sharedSearchInput').attr('placeholder', 'Search by plan ID, name, description...');
                // Show export button for subscriptions
                $('#sharedExportBtn').css('display', 'block');
                break;
                
            case 'logs':
                $('.filter-search').show();
                $('.filter-action-type').show();
                $('.filter-start-date').show();
                $('.filter-end-date').show();
                $('.filter-per-page').show();
                // Set search placeholder
                $('#sharedSearchInput').attr('placeholder', 'Search in descriptions...');
                // Hide export button for logs (it has its own export button in logs_filter.php)
                $('#sharedExportBtn').hide();
                // Change refresh button text to "Apply Filters" for logs
                if ($('#sharedRefreshBtnText').length) {
                    $('#sharedRefreshBtnText').text('Apply Filters');
                }
                if ($('#sharedRefreshBtn').length) {
                    $('#sharedRefreshBtn').removeClass('btn-info').addClass('btn-primary');
                }
                break;
                
            default:
                // Hide export button for unknown entity types
                $('#sharedExportBtn').hide();
                break;
        }
    };
    
    // ====================================
    // SAVE APPLICATION SETTINGS
    // ====================================
    $(document).on('click', '#saveAppSettingsBtn', function() {
        const btn = $(this);
        const originalText = btn.html();
        
        // Hide previous messages
        $('#appSettingsError, #appSettingsSuccess').addClass('d-none');
        
        // Get form values
        const data = {
            app_name: $('#appName').val(),
            timezone: $('#appTimezone').val(),
            session_timeout: $('#sessionTimeout').val(),
            records_per_page: $('#recordsPerPage').val()
        };
        
        // Validation
        if (!data.app_name || data.app_name.trim() === '') {
            $('#appSettingsError').find('span').text('Application name is required');
            $('#appSettingsError').removeClass('d-none');
            return;
        }
        
        if (!data.session_timeout) {
            $('#appSettingsError').find('span').text('Session timeout is required');
            $('#appSettingsError').removeClass('d-none');
            return;
        }
        
        if (!data.records_per_page) {
            $('#appSettingsError').find('span').text('Records per page is required');
            $('#appSettingsError').removeClass('d-none');
            return;
        }
        
        // Show loading
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        
        // AJAX request
        $.ajax({
            url: BASE_URL + 'profile/save-app-settings',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    showSuccessModal('Settings Saved', 'Application settings have been updated successfully.');
                    
                    // Update global JS variable
                    const newPerPage = parseInt(data.records_per_page);
                    window.APP_RECORDS_PER_PAGE = newPerPage;
                    
                    // Dispatch event for real-time update across all active table components
                    const event = new CustomEvent('app-records-per-page-updated', { 
                        detail: { perPage: newPerPage } 
                    });
                    document.dispatchEvent(event);
                } else {
                    $('#appSettingsError').find('span').text(response.message || 'Failed to save settings');
                    $('#appSettingsError').removeClass('d-none');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                $('#appSettingsError').find('span').text(response?.message || 'An error occurred. Please try again.');
                $('#appSettingsError').removeClass('d-none');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // ====================================
    // SAVE DATABASE CONFIGURATION
    // ====================================
    $(document).on('click', '#saveDatabaseConfigBtn', function() {
        const btn = $(this);
        const originalText = btn.html();
        
        // Hide previous messages
        $('#databaseSettingsError, #databaseSettingsSuccess').addClass('d-none');
        
        // Get form values
        const data = {
            db_host: $('#dbHost').val(),
            db_port: $('#dbPort').val(),
            db_name: $('#dbName').val(),
            db_username: $('#dbUsername').val(),
            db_password: $('#dbPassword').val()
        };
        
        // Validation
        if (!data.db_host || data.db_host.trim() === '') {
            $('#databaseSettingsError').find('span').text('Database host is required');
            $('#databaseSettingsError').removeClass('d-none');
            return;
        }
        
        if (!data.db_name || data.db_name.trim() === '') {
            $('#databaseSettingsError').find('span').text('Database name is required');
            $('#databaseSettingsError').removeClass('d-none');
            return;
        }
        
        if (!data.db_username || data.db_username.trim() === '') {
            $('#databaseSettingsError').find('span').text('Database username is required');
            $('#databaseSettingsError').removeClass('d-none');
            return;
        }
        
        // Show loading
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        
        // AJAX request
        $.ajax({
            url: BASE_URL + 'profile/save-database-config',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $('#databaseSettingsSuccess').find('span').text(response.message || 'Database configuration saved successfully!');
                    $('#databaseSettingsSuccess').removeClass('d-none');
                } else {
                    $('#databaseSettingsError').find('span').text(response.message || 'Failed to save configuration');
                    $('#databaseSettingsError').removeClass('d-none');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                $('#databaseSettingsError').find('span').text(response?.message || 'An error occurred. Please try again.');
                $('#databaseSettingsError').removeClass('d-none');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ====================================
    // GLOBAL MODAL HELPERS
    // ====================================
    
    /**
     * Show success modal with title and message
     */
    window.showSuccessModal = function(title, message) {
        if (!$('#successModal').length) {
            console.error('Success modal not found in DOM');
            alert(title + ': ' + message);
            return;
        }


        try {
            $('#successModalTitle span').text(title);
            $('#successModalMessage').text(message);
            
            const modalEl = document.getElementById('successModal');
            
            // Small delay to allow any previous modal backdrops to fully transition out
            // This prevents backdrop timing conflicts when showing success modal after delete modal
            setTimeout(() => {
                // Dispose of any existing instance to prevent backdrop stacking
                const existingInstance = bootstrap.Modal.getInstance(modalEl);
                if (existingInstance) {
                    existingInstance.dispose();
                }
                
                // Create a fresh modal instance
                const bsModal = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: true,
                    focus: true
                });
                
                bsModal.show();
            }, 150); // Small delay to ensure clean backdrop transition
        } catch (error) {
            console.error('Error showing success modal:', error);
            alert(title + ': ' + message);
        }
    };


    /**
     * Open unified delete confirmation modal
     */
    window.openDeleteModal = function(id, label, entityType = 'users') {
        if (!$('#deleteConfirmModal').length) {
            console.error('Delete confirm modal not found in DOM');
            return;
        }

        $('#deleteEntityType').val(entityType);
        $('#deleteEntityId').val(id);
        $('#deleteEntityLabel').text(label);

        // Blur any active element
        if (document.activeElement && document.activeElement.blur) {
            document.activeElement.blur();
        }

        try {
            const modalEl = $('#deleteConfirmModal')[0];
            const bsModal = new bootstrap.Modal(modalEl, {
                backdrop: true,
                keyboard: true,
                focus: false
            });
            bsModal.show();
        } catch (error) {
            console.error('Error opening delete modal:', error);
        }
    };

    /**
     * Global click handler for delete confirmation button
     * Calls the window.confirmDelete function which can be overridden by individual pages
     */
    $(document).on('click', '#confirmDeleteBtn', function() {
        if (typeof window.confirmDelete === 'function') {
            window.confirmDelete();
        } else {
            console.error('confirmDelete function not defined');
        }
    });
</script>
