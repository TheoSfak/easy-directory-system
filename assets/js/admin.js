/**
 * Easy Directory System - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
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
            if (!confirm('Enable all categories on this page?')) {
                return;
            }
            
            const $button = $(this);
            $button.prop('disabled', true).text('Enabling...');
            
            const toggles = $('.category-toggle:not(:checked)');
            let completed = 0;
            
            toggles.each(function() {
                const termId = $(this).data('term-id');
                const $toggle = $(this);
                
                $.ajax({
                    url: edsAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'eds_toggle_category',
                        nonce: edsAjax.nonce,
                        term_id: termId,
                        enabled: true
                    },
                    success: function(response) {
                        if (response.success) {
                            $toggle.prop('checked', true);
                        }
                    },
                    complete: function() {
                        completed++;
                        if (completed === toggles.length) {
                            $button.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Enable All Categories');
                            alert('All categories have been enabled!');
                            location.reload();
                        }
                    }
                });
            });
            
            if (toggles.length === 0) {
                $button.prop('disabled', false);
                alert('All categories are already enabled!');
            }
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
            syncWooCommerce('from_woo');
        });
        
        function syncWooCommerce(direction) {
            const button = direction === 'to_woo' ? $('.eds-sync-to-woo') : $('.eds-sync-from-woo');
            button.prop('disabled', true).text('Syncing...');
            
            $.ajax({
                url: edsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eds_sync_woocommerce',
                    nonce: edsAjax.nonce,
                    direction: direction
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
