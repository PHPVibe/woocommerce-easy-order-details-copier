(function () {
    'use strict';

    var lastTemplateTextarea = null;

    function i18n(key, fallback) {
        return (window.WUODC && window.WUODC[key]) || fallback;
    }

    function cleanText(text) {
        text = String(text || '');

        if (window.WUODC && window.WUODC.cleanCopiedText) {
            text = text.replace(/\r\n/g, '\n');
            text = text.replace(/[ \t]+\n/g, '\n');
            text = text.replace(/\n{3,}/g, '\n\n');
            text = text.replace(/[ \t]{2,}/g, ' ');
            text = text.trim();
        }

        return text;
    }

    function getText(button) {
        var sourceId = button.getAttribute('data-wuodc-source');
        var source;
        var control;
        var input;
        var template;
        var row;

        if (sourceId) {
            source = document.getElementById(sourceId);
            return source ? cleanText(source.value || source.textContent || '') : '';
        }

        template = button.getAttribute('data-wuodc-list-template');
        if (template) {
            row = button.closest('.wuodc-list-copy');
            source = row ? row.querySelector('.wuodc-list-copy-source[data-wuodc-template="' + template + '"]') : null;
            return source ? cleanText(source.value || source.textContent || '') : '';
        }

        control = button.closest('.wuodc-copy-control');
        input = control ? control.querySelector('.wuodc-copy-value') : null;

        return input ? cleanText(input.value) : '';
    }

    function fallbackCopy(text) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', 'readonly');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.top = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();

        var copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (e) {
            copied = false;
        }

        document.body.removeChild(textarea);

        return copied ? Promise.resolve() : Promise.reject();
    }

    function copyText(text) {
        text = cleanText(text);

        if (!text) {
            return Promise.reject(new Error('empty'));
        }

        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }

        return fallbackCopy(text);
    }

    function ensureGlobalToast() {
        var toastBox = document.querySelector('.wuodc-global-toast');

        if (!toastBox) {
            toastBox = document.createElement('div');
            toastBox.className = 'wuodc-global-toast';
            toastBox.setAttribute('aria-live', 'polite');
            toastBox.setAttribute('aria-atomic', 'true');
            document.body.appendChild(toastBox);
        }

        return toastBox;
    }

    function toast(button, message, isError) {
        var panel = button ? button.closest('.wuodc-panel') : null;
        var toastBox = panel ? panel.querySelector('.wuodc-toast') : null;

        if (!toastBox) {
            toastBox = ensureGlobalToast();
        }

        toastBox.textContent = message;
        toastBox.classList.toggle('is-error', !!isError);
        toastBox.classList.add('is-visible');

        window.clearTimeout(toastBox._wuodcTimer);
        toastBox._wuodcTimer = window.setTimeout(function () {
            toastBox.classList.remove('is-visible');
        }, 1500);
    }

    function setCopiedState(button, message, isError) {
        var icon = button ? button.querySelector('.dashicons') : null;
        var originalClass = button ? (button.getAttribute('data-original-icon') || 'dashicons-admin-page') : 'dashicons-admin-page';

        if (button && !button.getAttribute('data-original-icon') && icon) {
            Array.prototype.some.call(icon.classList, function (className) {
                if (className.indexOf('dashicons-') === 0 && className !== 'dashicons') {
                    originalClass = className;
                    return true;
                }
                return false;
            });
            button.setAttribute('data-original-icon', originalClass);
        }

        if (icon) {
            icon.classList.remove(originalClass, 'dashicons-yes-alt', 'dashicons-warning');
            icon.classList.add(isError ? 'dashicons-warning' : 'dashicons-yes-alt');
        }

        if (button) {
            button.classList.toggle('is-copied', !isError);
            button.classList.toggle('is-error', !!isError);
            button.disabled = false;
        }

        toast(button, message, isError);

        if (button) {
            window.clearTimeout(button._wuodcTimer);
            button._wuodcTimer = window.setTimeout(function () {
                if (icon) {
                    icon.classList.remove('dashicons-yes-alt', 'dashicons-warning');
                    icon.classList.add(originalClass);
                }
                button.classList.remove('is-copied', 'is-error');
            }, 1100);
        }
    }

    function setupClipboard() {
        document.addEventListener('click', function (event) {
            var button = event.target.closest('.wuodc-copy-button, .wuodc-quick-copy-button, .wuodc-list-copy-button');
            var text;

            if (!button) {
                return;
            }

            event.preventDefault();
            text = getText(button);
            button.disabled = true;

            copyText(text).then(function () {
                setCopiedState(button, i18n('copied', 'Copied'), false);
            }).catch(function (error) {
                setCopiedState(button, error && error.message === 'empty' ? i18n('nothingToCopy', 'Nothing available to copy.') : i18n('failed', 'Could not copy'), true);
            });
        });
    }

    function updateFieldOrder(list) {
        var wrap = list.closest('.wuodc-settings-card');
        var input = wrap ? wrap.querySelector('.wuodc-field-order-input') : document.querySelector('.wuodc-field-order-input');
        var ids = [];

        list.querySelectorAll('.wuodc-sort-item').forEach(function (item) {
            ids.push(item.getAttribute('data-field-id'));
        });

        if (input) {
            input.value = ids.join(',');
        }
    }

    function setupFieldToggleActions() {
        var actions = document.querySelector('[data-wuodc-field-toggle-actions]');
        var list = document.querySelector('[data-wuodc-sort-list]');

        if (!actions || !list) {
            return;
        }

        actions.addEventListener('click', function (event) {
            var enableButton = event.target.closest('[data-wuodc-fields-enable]');
            var disableButton = event.target.closest('[data-wuodc-fields-disable]');

            if (!enableButton && !disableButton) {
                return;
            }

            event.preventDefault();
            list.querySelectorAll('.wuodc-sort-enabled input[type="checkbox"]').forEach(function (checkbox) {
                checkbox.checked = !!enableButton;
            });
        });
    }

    function setupDerivedFieldExamples() {
        var button = document.querySelector('[data-wuodc-street-example]');
        var list = document.querySelector('[data-wuodc-extractor-list]');
        var rows;
        var pattern = '/^(.*?)\\s+(?:no\\.?|nr\\.?|#)\\s*(\\d+[A-Za-z]?)$/iu';

        if (!button || !list) {
            return;
        }

        button.addEventListener('click', function (event) {
            event.preventDefault();
            rows = list.querySelectorAll('[data-wuodc-extractor-row]');

            [
                { label: 'Street', group: '1' },
                { label: 'Street Number', group: '2' }
            ].forEach(function (example, index) {
                var row = rows[index];
                if (!row) {
                    return;
                }
                var enabled = row.querySelector('.wuodc-extractor-enabled input[type="checkbox"]');
                var label = row.querySelector('[data-wuodc-extractor-label]');
                var source = row.querySelector('[data-wuodc-extractor-source]');
                var regex = row.querySelector('[data-wuodc-extractor-pattern]');
                var group = row.querySelector('[data-wuodc-extractor-group]');

                if (enabled) { enabled.checked = true; }
                if (label) { label.value = example.label; }
                if (source) { source.value = 'shipping_address_1'; }
                if (regex) { regex.value = pattern; }
                if (group) { group.value = example.group; }
            });

            rows[0] && rows[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }

    function setupSortableFields() {
        var list = document.querySelector('[data-wuodc-sort-list]');
        var dragged = null;

        if (!list) {
            return;
        }

        list.addEventListener('dragstart', function (event) {
            var item = event.target.closest('.wuodc-sort-item');
            if (!item) {
                return;
            }
            dragged = item;
            item.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', item.getAttribute('data-field-id'));
        });

        list.addEventListener('dragover', function (event) {
            var item = event.target.closest('.wuodc-sort-item');
            var box;
            var next;

            if (!dragged) {
                return;
            }

            event.preventDefault();

            if (!item || item === dragged) {
                return;
            }

            box = item.getBoundingClientRect();
            next = (event.clientY - box.top) > (box.height / 2);
            list.insertBefore(dragged, next ? item.nextSibling : item);
            updateFieldOrder(list);
        });

        list.addEventListener('dragend', function () {
            if (dragged) {
                dragged.classList.remove('is-dragging');
            }
            dragged = null;
            updateFieldOrder(list);
        });

        list.addEventListener('keydown', function (event) {
            var item = event.target.closest('.wuodc-sort-item');

            if (!item || !event.target.classList.contains('wuodc-sort-handle')) {
                return;
            }

            if (event.key === 'ArrowUp' && item.previousElementSibling) {
                event.preventDefault();
                list.insertBefore(item, item.previousElementSibling);
                updateFieldOrder(list);
            }

            if (event.key === 'ArrowDown' && item.nextElementSibling) {
                event.preventDefault();
                list.insertBefore(item.nextElementSibling, item);
                updateFieldOrder(list);
            }
        });

        list.querySelectorAll('.wuodc-sort-handle').forEach(function (handle) {
            handle.setAttribute('tabindex', '0');
            handle.setAttribute('role', 'button');
        });

        updateFieldOrder(list);
    }

    function setupOrderListMenus() {
        document.addEventListener('click', function (event) {
            var toggle = event.target.closest('.wuodc-list-menu-toggle');
            var wrap;

            if (!toggle) {
                if (!event.target.closest('.wuodc-list-copy')) {
                    document.querySelectorAll('.wuodc-list-copy.is-open').forEach(function (openWrap) {
                        openWrap.classList.remove('is-open');
                        var openToggle = openWrap.querySelector('.wuodc-list-menu-toggle');
                        if (openToggle) {
                            openToggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                }
                return;
            }

            event.preventDefault();
            wrap = toggle.closest('.wuodc-list-copy');
            if (!wrap) {
                return;
            }

            document.querySelectorAll('.wuodc-list-copy.is-open').forEach(function (openWrap) {
                if (openWrap !== wrap) {
                    openWrap.classList.remove('is-open');
                    var openToggle = openWrap.querySelector('.wuodc-list-menu-toggle');
                    if (openToggle) {
                        openToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            wrap.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', wrap.classList.contains('is-open') ? 'true' : 'false');
        });
    }

    function setupBulkToolbar() {
        var toolbar = document.querySelector('[data-wuodc-list-bulk]');
        var table = document.querySelector('.wp-list-table');

        if (!toolbar || !table) {
            return;
        }

        toolbar.hidden = false;
        table.parentNode.insertBefore(toolbar, table);

        toolbar.addEventListener('click', function (event) {
            var button = event.target.closest('.wuodc-bulk-copy-button');
            var template;
            var parts = [];

            if (!button) {
                return;
            }

            event.preventDefault();
            template = button.getAttribute('data-wuodc-bulk-template');

            table.querySelectorAll('tbody th.check-column input[type="checkbox"]:checked, tbody td.check-column input[type="checkbox"]:checked').forEach(function (checkbox) {
                var tr = checkbox.closest('tr');
                var source = tr ? tr.querySelector('.wuodc-list-copy-source[data-wuodc-template="' + template + '"]') : null;
                var value = source ? cleanText(source.value || source.textContent || '') : '';

                if (value) {
                    parts.push(value);
                }
            });

            if (!parts.length) {
                toast(button, i18n('nothingSelected', 'Select one or more orders first.'), true);
                return;
            }

            button.disabled = true;
            copyText(parts.join('\n\n---\n\n')).then(function () {
                setCopiedState(button, i18n('bulkCopied', 'Selected orders copied'), false);
            }).catch(function () {
                setCopiedState(button, i18n('failed', 'Could not copy'), true);
            });
        });
    }

    function renderTemplatePreview(textarea) {
        var box = textarea.closest('.wuodc-template-box');
        var preview = box ? box.querySelector('.wuodc-template-preview-code') : null;
        var variables = (window.WUODC && window.WUODC.sampleVariables) || {};
        var value = textarea.value || '';

        Object.keys(variables).forEach(function (key) {
            value = value.split(key).join(variables[key]);
        });

        value = value.replace(/\{meta:[A-Za-z0-9_\-.]+\}/g, 'Custom meta value');
        value = cleanText(value);

        if (preview) {
            preview.textContent = value || '—';
        }
    }

    function setupTemplatePreview() {
        var textareas = document.querySelectorAll('[data-wuodc-template-content]');

        textareas.forEach(function (textarea) {
            renderTemplatePreview(textarea);
            textarea.addEventListener('input', function () {
                renderTemplatePreview(textarea);
            });
            textarea.addEventListener('focus', function () {
                lastTemplateTextarea = textarea;
            });
        });

        document.addEventListener('click', function (event) {
            var chip = event.target.closest('.wuodc-variable-chip');
            var variable;
            var textarea;
            var start;
            var end;

            if (!chip) {
                return;
            }

            event.preventDefault();
            variable = chip.getAttribute('data-wuodc-variable') || '';
            textarea = lastTemplateTextarea || document.querySelector('[data-wuodc-template-content]');

            if (!textarea || !variable) {
                return;
            }

            textarea.focus();
            start = textarea.selectionStart || textarea.value.length;
            end = textarea.selectionEnd || textarea.value.length;
            textarea.value = textarea.value.slice(0, start) + variable + textarea.value.slice(end);
            textarea.selectionStart = textarea.selectionEnd = start + variable.length;
            renderTemplatePreview(textarea);
        });
    }

    function init() {
        setupClipboard();
        setupFieldToggleActions();
        setupDerivedFieldExamples();
        setupSortableFields();
        setupOrderListMenus();
        setupBulkToolbar();
        setupTemplatePreview();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
