/**
 * Easy Directory System - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Greek to Greeklish conversion
        function greekToGreeklish(text) {
            const greekMap = {
                'α':'a','ά':'a','Α':'a','Ά':'a','β':'b','Β':'b','γ':'g','Γ':'g','δ':'d','Δ':'d',
                'ε':'e','έ':'e','Ε':'e','Έ':'e','ζ':'z','Ζ':'z','η':'i','ή':'i','Η':'i','Ή':'i',
                'θ':'th','Θ':'th','ι':'i','ί':'i','ϊ':'i','ΐ':'i','Ι':'i','Ί':'i','Ϊ':'i',
                'κ':'k','Κ':'k','λ':'l','Λ':'l','μ':'m','Μ':'m','ν':'n','Ν':'n','ξ':'ks','Ξ':'ks',
                'ο':'o','ό':'o','Ο':'o','Ό':'o','π':'p','Π':'p','ρ':'r','Ρ':'r','σ':'s','ς':'s','Σ':'s',
                'τ':'t','Τ':'t','υ':'y','ύ':'y','ϋ':'y','ΰ':'y','Υ':'y','Ύ':'y','Ϋ':'y',
                'φ':'f','Φ':'f','χ':'ch','Χ':'ch','ψ':'ps','Ψ':'ps','ω':'o','ώ':'o','Ω':'o','Ώ':'o'
            };
            return text.split('').map(char => greekMap[char] || char).join('');
        }
        
        // Function to validate slug
        function validateSlug(input) {
            let slug = input.value;
            let allowedChars = edsAjax.settings ? edsAjax.settings.allowed_url_chars : 'letters_numbers_underscores_hyphens';
            let pattern;
            
            // Convert to lowercase first
            slug = slug.toLowerCase();
            
            // Define regex pattern based on setting
            switch(allowedChars) {
                case 'letters_numbers':
                    pattern = /[^a-z0-9]/gi;
                    break;
                case 'letters_numbers_hyphens':
                    pattern = /[^a-z0-9\-]/gi;
                    break;
                case 'letters_numbers_underscores_hyphens_greek':
                    // Allow Greek characters - case insensitive
                    pattern = /[^a-z0-9_\-\u0370-\u03ff\u1f00-\u1fff]/gi;
                    break;
                case 'letters_numbers_underscores_hyphens_greeklish':
                    // Convert Greek to Greeklish first, then allow only standard chars
                    slug = greekToGreeklish(slug);
                    pattern = /[^a-z0-9_\-]/gi;
                    break;
                case 'letters_numbers_underscores_hyphens':
                default:
                    pattern = /[^a-z0-9_\-]/gi;
                    break;
            }
            
            // Remove invalid characters
            let cleaned = slug.replace(pattern, '');
            
            if (cleaned !== slug) {
                input.value = cleaned;
            }
        }
        
        // Watch slug field for changes using polling (handles IME input for Greek characters)
        const slugInput = document.getElementById('slug');
        if (slugInput) {
            let lastValue = slugInput.value;
            
            // Poll for changes every 100ms
            setInterval(function() {
                if (slugInput.value !== lastValue) {
                    lastValue = slugInput.value;
                    validateSlug(slugInput);
                }
            }, 100);
            
            // Also handle input event
            slugInput.addEventListener('input', function() {
                validateSlug(this);
            });
        }
        
        // Toggle category enabled/disabled
        $('.eds-toggle input').on('change', function() {
            const termId = $(this).data('term-id');
            const enabled = $(this).is(':checked');
            
            $.ajax({
                url: edsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eds_toggle_category',
                    nonce: edsAjax.nonce,
                    term_id: termId,
                    enabled: enabled
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Category status updated');
                    }
                },
                error: function() {
                    alert('Failed to update category status');
                }
            });
        });
        
        // Enable all categories button
        $('.eds-enable-all').on('click', function() {
            if (!confirm('Enable all categories and subcategories in this taxonomy?')) {
                return;
            }
            
            const $button = $(this);
            $button.prop('disabled', true).text('Enabling...');
            
            // Get taxonomy from URL or default
            const urlParams = new URLSearchParams(window.location.search);
            const taxonomy = urlParams.get('taxonomy') || 'category';
            
            $.ajax({
                url: edsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eds_enable_all_categories',
                    nonce: edsAjax.nonce,
                    taxonomy: taxonomy
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message || 'All categories and subcategories have been enabled!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.data.message || 'Failed to enable categories'));
                        $button.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Enable All Categories');
                    }
                },
                error: function() {
                    alert('Failed to enable categories');
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Enable All Categories');
                }
            });
        });
        
        // Delete category
        $('.eds-delete-category').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm(edsAjax.strings.confirm_delete)) {
                return;
            }
            
            const termId = $(this).data('term-id');
            const taxonomy = $(this).data('taxonomy') || 'category';
            const row = $(this).closest('tr');
            
            $.ajax({
                url: edsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eds_delete_category',
                    nonce: edsAjax.nonce,
                    term_id: termId,
                    taxonomy: taxonomy
                },
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });
        
        // Sync with WooCommerce
        $('.eds-sync-to-woo').on('click', function() {
            syncWooCommerce('to_woo');
        });
        
        $('.eds-sync-from-woo').on('click', function() {
            const button = $('.eds-sync-from-woo');
            const syncMode = edsAjax.settings ? edsAjax.settings.sync_mode : 'add_only';
            
            // Get sync mode description
            let syncModeText = '';
            switch(syncMode) {
                case 'add_only':
                    syncModeText = 'Add Only - Only new categories will be added, existing categories will NOT be removed';
                    break;
                case 'full_sync_confirm':
                    syncModeText = 'Full Sync - New categories will be added AND orphaned categories will be removed (with confirmation)';
                    break;
                case 'full_sync_auto':
                    syncModeText = 'Full Sync - New categories will be added AND orphaned categories will be removed (automatic)';
                    break;
            }
            
            // If mode is add_only, show info message and sync
            if (syncMode === 'add_only') {
                if (confirm('Sync Mode: ' + syncModeText + '\n\nDo you want to proceed?')) {
                    syncWooCommerce('from_woo', false);
                }
                return;
            }
            
            // If mode is full_sync_auto, show info and sync automatically
            if (syncMode === 'full_sync_auto') {
                if (confirm('Sync Mode: ' + syncModeText + '\n\nWARNING: This will automatically remove categories that no longer exist in WooCommerce!\n\nDo you want to proceed?')) {
                    syncWooCommerce('from_woo', true);
                }
                return;
            }
            
            // For full_sync_confirm, check for orphaned categories first
            button.prop('disabled', true).text('Checking...');
            
            $.ajax({
                url: edsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eds_get_orphaned_categories',
                    nonce: edsAjax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.orphaned.length > 0) {
                        // Show confirmation dialog with list of categories to be removed
                        let categoryList = response.data.orphaned.map(cat => '• ' + cat.name).join('\n');
                        let message = 'Sync Mode: ' + syncModeText + '\n\n' +
                                     'The following categories exist in Easy Directory but NOT in WooCommerce and will be REMOVED:\n\n' + 
                                     categoryList + 
                                     '\n\nDo you want to proceed?';
                        
                        if (confirm(message)) {
                            syncWooCommerce('from_woo', true);
                        } else {
                            button.prop('disabled', false).text('Sync from WooCommerce');
                        }
                    } else {
                        // No orphaned categories, but still inform about sync mode
                        let message = 'Sync Mode: ' + syncModeText + '\n\n' +
                                     'No orphaned categories found. All categories will be synced.\n\n' +
                                     'Do you want to proceed?';
                        
                        if (confirm(message)) {
                            syncWooCommerce('from_woo', false);
                        } else {
                            button.prop('disabled', false).text('Sync from WooCommerce');
                        }
                    }
                },
                error: function() {
                    button.prop('disabled', false).text('Sync from WooCommerce');
                    alert('Failed to check for orphaned categories');
                }
            });
        });
        
        function syncWooCommerce(direction, removeOrphaned) {
            const button = direction === 'to_woo' ? $('.eds-sync-to-woo') : $('.eds-sync-from-woo');
            button.prop('disabled', true).text('Syncing...');
            
            $.ajax({
                url: edsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eds_sync_woocommerce',
                    nonce: edsAjax.nonce,
                    direction: direction,
                    remove_orphaned: removeOrphaned || false
                },
                success: function(response) {
                    if (response.success) {
                        alert(edsAjax.strings.sync_success + '\n' + response.data.message);
                        location.reload();
                    } else {
                        alert(edsAjax.strings.sync_error + '\n' + response.data.message);
                    }
                },
                complete: function() {
                    button.prop('disabled', false).text(direction === 'to_woo' ? 'Sync to WooCommerce' : 'Sync from WooCommerce');
                }
            });
        }
        
        // Sortable rows
        if ($('.eds-table tbody.sortable').length) {
            $('.eds-table tbody.sortable').sortable({
                handle: '.drag-handle',
                cursor: 'move',
                axis: 'y',
                opacity: 0.8,
                helper: function(e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                update: function(event, ui) {
                    const positions = {};
                    $(this).find('tr').each(function(index) {
                        const termId = $(this).data('term-id');
                        if (termId) {
                            positions[termId] = index;
                        }
                    });
                    
                    $.ajax({
                        url: edsAjax.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'eds_update_position',
                            nonce: edsAjax.nonce,
                            positions: positions
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update position column values
                                $('.eds-table tbody.sortable tr').each(function(index) {
                                    $(this).find('td:eq(6)').text(index);
                                });
                            }
                        }
                    });
                }
            });
        }
        
        // Language tabs
        $('.eds-lang-tab').on('click', function() {
            const lang = $(this).data('lang');
            
            $('.eds-lang-tab').removeClass('active');
            $('.eds-lang-content').removeClass('active');
            
            $(this).addClass('active');
            $('.eds-lang-content[data-lang="' + lang + '"]').addClass('active');
        });
        
        // Image upload
        $('.eds-upload-image').on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const inputId = button.data('input-id');
            const previewId = button.data('preview-id');
            
            const frame = wp.media({
                title: 'Select Image',
                button: { text: 'Use this image' },
                multiple: false
            });
            
            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                $('#' + inputId).val(attachment.id);
                $('#' + previewId).html('<img src="' + attachment.url + '" />');
            });
            
            frame.open();
        });
        
        // Refresh statistics
        $('.eds-refresh-stats').on('click', function() {
            const button = $(this);
            button.prop('disabled', true);
            
            $.ajax({
                url: edsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eds_get_statistics',
                    nonce: edsAjax.nonce,
                    taxonomy: $('input[name="taxonomy"]').val() || 'category'
                },
                success: function(response) {
                    if (response.success) {
                        updateStatistics(response.data);
                    }
                },
                complete: function() {
                    button.prop('disabled', false);
                }
            });
        });
        
        function updateStatistics(stats) {
            $('.stat-disabled').text(stats.disabled);
            $('.stat-empty').text(stats.empty);
            $('.stat-top').text(stats.top_category ? stats.top_category.name : 'N/A');
            $('.stat-average').text(stats.average_products);
        }
        
        // SEO preview update
        $('input[name="meta_title"], textarea[name="meta_description"]').on('input', function() {
            const title = $('input[name="meta_title"]').val() || $('input[name="name"]').val() || 'Category Title';
            const desc = $('textarea[name="meta_description"]').val() || 'Category description will appear here...';
            
            $('.eds-seo-preview-title').text(title);
            $('.eds-seo-preview-desc').text(desc.substring(0, 160) + (desc.length > 160 ? '...' : ''));
        });
        
        // Character counter for meta fields
        $('.seo-meta-title').on('input', function() {
            const count = $(this).val().length;
            const max = $(this).attr('maxlength') || 70;
            $(this).next('.char-count').text(count + ' / ' + max + ' characters used (recommended)');
        });
        
        $('.seo-meta-description').on('input', function() {
            const count = $(this).val().length;
            const max = $(this).attr('maxlength') || 160;
            $(this).next('.char-count').text(count + ' / ' + max + ' characters used (recommended)');
        });
        
        // Real-time SEO preview update
        $('.seo-meta-title').on('input', function() {
            const title = $(this).val() || 'Category Title';
            $('.eds-seo-preview-title').text(title);
        });
        
        $('.seo-meta-description').on('input', function() {
            const desc = $(this).val() || 'Category description will appear here...';
            $('.eds-seo-preview-desc').text(desc.substring(0, 160) + (desc.length > 160 ? '...' : ''));
        });
    });
    
})(jQuery);
