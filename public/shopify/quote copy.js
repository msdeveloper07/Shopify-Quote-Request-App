(function() {
    console.warn('Running!!')
    // prevent double init
    if (window.__QUOTE_INIT__) return;
    window.__QUOTE_INIT__ = true;

    // ---------------------------
    // CONFIG
    // ---------------------------
    const BASE_URL = 'https://sees-rider-modifications-consumer.trycloudflare.com';
    const CSS_URL = `${BASE_URL}/shopify/quote.css`;
    const CARD_QUERY = [
        'div.product-detail-get-quote',
        '.product-card__content'
    ].join(',');

    // ---------------------------
    // UTILITIES
    // ---------------------------
    function loadExternalCSS() {
        if (!document.querySelector(`link[href="${CSS_URL}"]`)) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = CSS_URL;
            link.type = 'text/css';
            link.media = 'all';
            document.head.appendChild(link);
        }
    }

    function onDomReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function moneyFromCents(cents) {
        const amount = Number(cents || 0) / 100;
        const currency =
            (window.Shopify && window.Shopify.currency && window.Shopify.currency.active) ||
            (window.Shopify && window.Shopify.Checkout && window.Shopify.Checkout.currency) ||
            window.shopCurrency ||
            'USD';
        try {
            return new Intl.NumberFormat(undefined, {
                style: 'currency',
                currency
            }).format(amount);
        } catch {
            return `$${amount.toFixed(2)}`;
        }
    }

    function showToast(message, type = 'green') {
        let toast = document.getElementById('quoteToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'quoteToast';
            toast.style.cssText = 'display:none;position:fixed;top:20px;right:20px;background:#16a34a;color:#fff;padding:10px 20px;border-radius:5px;z-index:99999;';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.background = type === 'green' ? '#16a34a' : '#dc2626';
        toast.style.display = 'block';
        setTimeout(() => (toast.style.display = 'none'), 4000);
    }

    // ---------------------------
    // PRODUCT FETCH
    // ---------------------------
    const productCache = new Map(); 
    async function fetchProductByUrl(productUrl) {
        try {
            const m = productUrl && productUrl.match(/\/products\/([^/?#]+)/i);
            const handle = m ? m[1] : null;
            return await fetchProductByHandle(handle);
        } catch {
            return null;
        }
    }

    async function fetchProductByHandle(handle) {
        if (!handle) return null;
        if (productCache.has(handle)) return productCache.get(handle);
        const res = await fetch(`/products/${handle}.js`, {
            credentials: 'same-origin'
        });
        if (!res.ok) return null;
        const json = await res.json();
        productCache.set(handle, json);
        return json;
    }

    function bindExistingGetQuoteButtons(scope = document) {
        scope.querySelectorAll('.get_quote_btn:not([data-quote-bound="1"])').forEach(btn => {
            // mark as bound to avoid duplicates
            btn.setAttribute('data-quote-bound', '1');

            // try to ensure we have the handle
            if (!btn.dataset.productHandle) {
                const card = btn.closest('article, .card, .product-card, .grid__item, .collection__item, .product-grid-item, li, div');
                const a = card && findProductAnchor(card);
                const m = a && a.href && a.href.match(/\/products\/([^/?#]+)/i);
                if (m) btn.dataset.productHandle = m[1];
            }

            // stop theme navigation and open modal
            const blockEarly = (e) => {
                e.preventDefault();
                e.stopPropagation();
            };
            ['mousedown', 'mouseup', 'touchstart', 'touchend'].forEach(evt =>
                btn.addEventListener(evt, blockEarly, {
                    capture: true
                })
            );

            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                const handle = btn.dataset.productHandle;
                if (!handle) return showToast('Unable to load product details', 'red');

                const product = await fetchProductByHandle(handle);
                if (!product) return showToast('Unable to load product details', 'red');

                openQuoteModalWithProduct(product);
            });
        });
    }


    function getPrimaryImageFromProduct(productJson) {
        if (!productJson) return '';
        const vWithImg = (productJson.variants || []).find(v => v.featured_image && v.featured_image.src);
        if (vWithImg?.featured_image?.src) return vWithImg.featured_image.src;
        if (productJson.images && productJson.images.length) return productJson.images[0];
        return '';
    }

    // ---------------------------
    // MODAL
    // ---------------------------
    async function openQuoteModalWithProduct(productJson) {
        const productTitle = productJson?.title || document.querySelector('h1')?.innerText || '';
        const productImg = getPrimaryImageFromProduct(productJson) || (document.querySelector('.product__media img, .product-gallery img, img')?.src || '');

        customerData = await fetchCustomerDataFromBackend();

        const modalHtml = `
    <div id="quoteModal" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:20px;max-width:600px;width:90%;position:relative;border-radius:8px;">
        <button onclick="document.getElementById('quoteModal').remove()" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:20px;cursor:pointer;">✕</button>
        <div class="boxheading">Request a quote</div>
        <div class="logoDiv">
            <img id="modalProductImage" src="${productImg}" alt="Product Image" style="max-height:100px;transition:opacity 0.3s ease;" />
        </div>
        <h2 style="margin-bottom:10px; font-weight: 400; text-align: center; margin-top: 0;">${productTitle}</h2>
        <form id="quoteForm">

            <div class="nameDivs">
                <div style="margin-bottom:10px;width: 50%;float: left;padding-right: 5px;">
                <label style="font-weight:bold;display:block;margin-bottom:4px;">Select Variant</label>
                <select name="selected_variant" id="variantDropdown" style="width:100%;padding:8px;" required>
                    <option value="">Select a variant</option>
                </select>
                <div id="selected_variant_error" style="color:red;font-size:13px;margin-top:4px;"></div>
                </div>

                <div style="margin-bottom:10px;width: 50%;float: left;padding-right: 5px;">
                <label style="font-weight:bold; display:block; margin-bottom:4px;">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="1" max="99" value="1" style="width:100%;padding:8px;" />
                    <div id="quantity_error" style="color:red;font-size:13px;margin-top:4px;"></div>          
                </div>
            </div>

            <div class="nameDivs">
            <div style="margin-bottom:10px;width: 50%;float: left;padding-right: 5px;">
                <label style="font-weight:bold; display:block; margin-bottom:4px;">First Name</label>
                <input name="first_name" placeholder="First Name" style="width:100%;padding:8px;" />
                <div id="first_name_error" style="color:red;font-size:13px;margin-top:4px;"></div>
            </div>

            <div style="margin-bottom:10px;width: 50%;float: left;padding-right: 5px;">
                <label style="font-weight:bold; display:block; margin-bottom:4px;">Last Name</label>
                <input name="last_name" placeholder="Last Name" style="width:100%;padding:8px;" />
                <div id="last_name_error" style="color:red;font-size:13px;margin-top:4px;"></div>
            </div>
            </div>

            <div class="nameDivs">
            <div style="margin-bottom:10px;width: 50%;float: left;padding-right: 5px;">
                <label style="font-weight:bold; display:block; margin-bottom:4px;">Your Email</label>
                <input name="email" type="email" placeholder="Your Email" style="width:100%;padding:8px;" />
                <div id="email_error" style="color:red;font-size:13px;margin-top:4px;"></div>
            </div>

            <div style="margin-bottom:10px;width: 50%;float: left;padding-right: 5px;">
                <label style="font-weight:bold; display:block; margin-bottom:4px;">Phone</label>
                <input name="phone" type="tel" placeholder="Phone number" style="width:100%;padding:8px;" />
                <div id="phone_error" style="color:red;font-size:13px;margin-top:4px;"></div>
            </div>
            </div>

            <div style="margin-bottom:10px;">
            <label style="font-weight:bold; display:block; margin-bottom:4px;">Any comments or suggestions?</label>
            <textarea name="comments" placeholder="Any comments or suggestions?" style="width:100%;padding:8px;height:80px;"></textarea>
            <div id="comments_error" style="color:red;font-size:13px;margin-top:4px;"></div>
            </div>

            <div id="loader" style="display:none;text-align:center;margin-top:10px;">
            <img src="https://i.imgur.com/llF5iyg.gif" alt="Loading..." style="width:30px;height:30px;" />
            </div>

            <button type="submit" style="padding:10px 20px;background:#000;color:#fff;border:none;cursor:pointer;width:100%;">Submit</button>
        </form>
        </div>
    </div>
    `;

        const wrap = document.createElement('div');
        wrap.innerHTML = modalHtml;
        document.body.appendChild(wrap);

        // Get references to elements
        const form = document.getElementById('quoteForm');
        const modalImage = document.getElementById('modalProductImage');
        
        // PRE-FILL CUSTOMER DATA IF AVAILABLE
        if (customerData && typeof customerData === 'object') {
            const firstNameInput = form.querySelector('input[name="first_name"]');
            const lastNameInput = form.querySelector('input[name="last_name"]');
            const emailInput = form.querySelector('input[name="email"]');
            const phoneInput = form.querySelector('input[name="phone"]');
            
            if (firstNameInput && customerData.first_name) {
                firstNameInput.value = customerData.first_name;
                firstNameInput.style.background = '#f0f8ff';
            }
            if (lastNameInput && customerData.last_name) {
                lastNameInput.value = customerData.last_name;
                lastNameInput.style.background = '#f0f8ff';
            }
            if (emailInput && customerData.email) {
                emailInput.value = customerData.email;
                emailInput.style.background = '#f0f8ff';
            }
            if (phoneInput && customerData.phone) {
                phoneInput.value = customerData.phone;
                phoneInput.style.background = '#f0f8ff';
            }
        }

        // Hidden inputs
        const titleInput = document.createElement('input');
        titleInput.type = 'hidden';
        titleInput.name = 'product_title';
        titleInput.value = productTitle;
        form.appendChild(titleInput);

        let priceInput = form.querySelector('input[name="variant_price"]');
        if (!priceInput) {
            priceInput = document.createElement('input');
            priceInput.type = 'hidden';
            priceInput.name = 'variant_price';
            form.appendChild(priceInput);
        }

        const imageInput = document.createElement('input');
        imageInput.type = 'hidden';
        imageInput.name = 'product_image';
        imageInput.value = productImg;
        form.appendChild(imageInput);

        const urlInput = document.createElement('input');
        urlInput.type = 'hidden';
        urlInput.name = 'product_url';
        urlInput.value = window.location.href;
        form.appendChild(urlInput);

        // Populate variants with image data
        try {
            const variantDropdown = document.getElementById('variantDropdown');
            variantDropdown.innerHTML = '<option value="">Select a variant</option>';
            const variantNameInput = document.createElement('input');
            variantNameInput.type = 'hidden';
            variantNameInput.name = 'variant_name';
            variantNameInput.value = '';
            form.appendChild(variantNameInput)

            if (productJson?.variants?.length) {
                productJson.variants.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = String(v.id);
                    opt.textContent = v.title ? `${v.title} - ${moneyFromCents(v.price)}` : moneyFromCents(v.price);
                    opt.dataset.priceCents = String(v.price || 0);
                    
                    opt.dataset.variantTitle = v.title || 'Default';

                    // Store variant image URL in dataset
                    if (v.featured_image && v.featured_image.src) {
                        opt.dataset.imageUrl = v.featured_image.src;
                    } else {
                        // Use default product image if variant has no image
                        opt.dataset.imageUrl = productImg;
                    }
                    
                    variantDropdown.appendChild(opt);
                });

                // Auto-select first variant if only one exists
                if (productJson.variants.length === 1) {
                    variantDropdown.selectedIndex = 1;
                    priceInput.value = (Number(productJson.variants[0].price || 0) / 100).toFixed(2);
                    
                    // Update image if first variant has one
                    if (productJson.variants[0].featured_image && productJson.variants[0].featured_image.src) {
                        modalImage.src = productJson.variants[0].featured_image.src;
                        imageInput.value = productJson.variants[0].featured_image.src;
                    }
                }
            } else {
                variantDropdown.innerHTML = '<option>No variants found</option>';
            }

            // Event listener for variant change - UPDATE BOTH PRICE AND IMAGE
            variantDropdown.addEventListener('change', () => {
                const selOpt = variantDropdown.selectedOptions[0];
                
                if (!selOpt || !selOpt.value) {
                    // Reset to default if no variant selected
                    modalImage.src = productImg;
                    imageInput.value = productImg;
                    priceInput.value = '';
                    variantNameInput.value = '';
                    return;
                }
                
                // Update price
                const cents = selOpt ? Number(selOpt.dataset.priceCents || 0) : 0;
                priceInput.value = (cents / 100).toFixed(2);
                
                variantNameInput.value = selOpt.dataset.variantTitle || '';
                
                // Update image with fade effect
                const newImageUrl = selOpt.dataset.imageUrl || productImg;
                
                if (newImageUrl && newImageUrl !== modalImage.src) {
                    // Fade out
                    modalImage.style.opacity = '0.3';
                    
                    // Change image after brief delay
                    setTimeout(() => {
                        modalImage.src = newImageUrl;
                        imageInput.value = newImageUrl;
                        
                        // Fade in
                        modalImage.style.opacity = '1';
                    }, 150);
                }
            });
            
        } catch (e) {
            console.error('Error loading variants:', e);
            const variantDropdown = document.getElementById('variantDropdown');
            if (variantDropdown) variantDropdown.innerHTML = '<option>Error loading variants</option>';
        }

        // Validation functions (unchanged)
        function validateField(input) {
            const name = input.name;
            const value = (input.value || '').trim();
            const errorEl = document.getElementById(`${name}_error`);
            if (!errorEl) return;

            errorEl.textContent = '';
            if (name === 'first_name' && !value) errorEl.textContent = 'First name is required.';
            else if (name === 'last_name' && !value) errorEl.textContent = 'Last name is required.';
            else if (name === 'email') {
                if (!value) errorEl.textContent = 'Email is required.';
                else if (!/^\S+@\S+\.\S+$/.test(value)) errorEl.textContent = 'Email format is invalid.';
            } else if (name === 'phone') {
                if (!value) errorEl.textContent = 'Phone is required.';
                else {
                    const digits = value.replace(/[^\d]/g, '');
                    if (digits.length < 7 || digits.length > 20) errorEl.textContent = 'Enter a valid phone number.';
                }
            } else if (name === 'comments' && value.length < 5) errorEl.textContent = 'Comment must be at least 5 characters.';
            else if (name === 'selected_variant' && !value) errorEl.textContent = 'Please select a variant.';
        }

        function validateForm() {
            const fields = ['first_name', 'last_name', 'email', 'phone', 'comments', 'selected_variant'];
            let focusField = null;
            fields.forEach(n => {
                const input = form.querySelector(`[name="${n}"]`);
                if (input) {
                    validateField(input);
                    if (!focusField && document.getElementById(`${n}_error`)?.textContent) focusField = input;
                }
            });
            if (focusField) focusField.focus();
            return !focusField;
        }

        form.querySelectorAll('input, textarea, select').forEach(i => {
            i.addEventListener('blur', () => validateField(i));
            i.addEventListener('input', () => validateField(i));
        });

        // Form submission (unchanged)
        form.onsubmit = async function(e) {
            e.preventDefault();
            if (!validateForm()) return;

            const data = Object.fromEntries(new FormData(form).entries());
            const shop = window.Shopify?.shop;
            if (!shop) {
                showToast('Unable to detect shop domain', 'red');
                return;
            }

            const loader = document.getElementById('loader');
            loader.style.display = 'block';
            
            try {
                const res = await fetch(`https://sees-rider-modifications-consumer.trycloudflare.com/proxy/quote-guard?shop=${shop}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const json = await res.json().catch(() => ({}));
                loader.style.display = 'none';

                if (res.ok && json.status === 'success') {
                    console.log('Success:', json);
                    showToast('Quote submitted successfully! We will contact you soon.', 'green');
                    
                    const modal = document.getElementById('quoteModal');
                    if (modal) modal.remove();
                    
                    return { success: true, data: json };
                } else {
                    console.error('Submission failed:', json);
                    showToast(json.message || 'Submission failed. Please try again.', 'red');
                    return { success: false, error: json };
                }

            } catch (err) {
                loader.style.display = 'none';
                showToast('Error submitting quote', 'red');
                console.error('Submit error:', err);
            }
        };
    }

    async function fetchCustomerDataFromBackend() {
        try {
            const shop = window.Shopify?.shop;
            if (!shop) return null;
            
            // Try to get customer ID from ShopifyAnalytics
            const customerId = window.ShopifyAnalytics?.meta?.page?.customerId;
            if (!customerId) return null;
            
            const response = await fetch(`${BASE_URL}/api/get-customer-data?shop=${shop}&customer_id=${customerId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) return null;
            
            const data = await response.json();
            return data;
            
        } catch (e) {
            console.log('Could not fetch customer from backend:', e);
            return null;
        }
    }

    // ---------------------------
    // GRID BUTTONS (injected)
    // ---------------------------
    function makeGridButton(productUrl) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'addToQuoteBtn';
        btn.textContent = 'Get a Quote';
        btn.style.cssText = [
            'padding:8px 12px',
            'background:#000',
            'color:#fff',
            'border:none',
            'cursor:pointer',
            'margin-top:8px',
            'width:100%',
            'position:relative',
            'z-index:5',
            'pointer-events:auto'
        ].join(';');
        btn.setAttribute('data-quote-bound', '1');

        const blockEarly = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };
        ['mousedown', 'mouseup', 'touchstart', 'touchend'].forEach(evt =>
            btn.addEventListener(evt, blockEarly, {
                capture: true
            })
        );

        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();

            const productData = await fetchProductByUrl(productUrl);
            if (!productData) return showToast('Unable to load product details', 'red');
            openQuoteModalWithProduct(productData);
        });

        return btn;
    }

    function findProductAnchor(containerEl) {
        return (
            containerEl.querySelector('a[href*="/products/"] img')?.closest('a') ||
            containerEl.querySelector('.product__image-wrapper a[href*="/products/"]') ||
            containerEl.querySelector('.product-card__image-with-placeholder-wrapper a[href*="/products/"]') ||
            containerEl.querySelector('a[href*="/products/"]')
        );
    }

    function placeButtonUnderImage(containerEl, buttonEl, productAnchor) {
        // If merchant provided a dedicated slot, use it.
        const customSlot = containerEl.querySelector('.get_quote');
        if (customSlot) {
            customSlot.appendChild(buttonEl);
            return true;
        }

        // Default: after the product link
        const holder = document.createElement('div');
        holder.style.cssText = 'margin-top:8px;';
        holder.appendChild(buttonEl);

        if (productAnchor && productAnchor.parentNode) {
            productAnchor.insertAdjacentElement('afterend', holder);
            return true;
        }

        // Last fallback: append to container
        containerEl.appendChild(holder);
        return false;
    }


    function injectButtonsIntoGrid(scope = document) {
        const candidates = scope.querySelectorAll(CARD_QUERY);
        candidates.forEach(container => {
            if (container.closest('form[action*="/cart/add"]')) return;

            const productAnchor = findProductAnchor(container);
            if (!productAnchor) return;

            if (productAnchor.dataset.quoteBound === '1') return;
            if (container.querySelector('.addToQuoteBtn[data-quote-bound="1"]')) return;

            productAnchor.dataset.quoteBound = '1';

            const btn = makeGridButton(productAnchor.href);
            placeButtonUnderImage(container, btn, productAnchor);
        });
    }

    // ---------------------------
    // BIND EXTENSION BLOCK BUTTON (.cb-quote-btn)
    // ---------------------------
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.cb-quote-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const handle = btn.dataset.productHandle || btn.getAttribute('data-product-handle') || '';
        if (!handle) return showToast('Missing product handle', 'red');

        const product = await fetchProductByHandle(handle);
        if (!product) return showToast('Unable to load product details', 'red');
        openQuoteModalWithProduct(product);
    });

    function observeDynamicGrids() {
        const observer = new MutationObserver(mutations => {
            mutations.forEach(m => {
                m.addedNodes.forEach(node => {
                    if (!(node instanceof HTMLElement)) return;
                    bindExistingGetQuoteButtons(node); // <- bind new theme buttons
                    injectButtonsIntoGrid(node); // <- inject ours if needed
                });
            });
        });
        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });
    }


    // ---------------------------
    // PRODUCT PAGE BUTTON INJECTION
    // ---------------------------
    function injectButtonOnProductPage() {
        // Check if we're on a product page
        if (!window.location.pathname.includes('/products/')) return;
        
        // Target the specific area in your HTML where button should appear
        const targetSelectors = [
            '.product-details .group-block-content',  // Based on your HTML
            'form[action*="/cart/add"]',
            '.product__info-container',
            '.product-form',
            '#ProductInformation-template',
            '.product-information'
        ];
        
        let targetContainer = null;
        for (const selector of targetSelectors) {
            targetContainer = document.querySelector(selector);
            if (targetContainer) break;
        }
        
        if (!targetContainer) {
            console.warn('Product page container not found');
            return;
        }
        
        // Check if button already exists
        if (targetContainer.querySelector('.product-detail-get-quote-btn')) {
            return;
        }
        
        // Get product handle from URL
        const handleMatch = window.location.pathname.match(/\/products\/([^/?#]+)/);
        const handle = handleMatch ? handleMatch[1] : null;
        
        if (!handle) {
            console.warn('Could not extract product handle from URL');
            return;
        }
        
        // Create button wrapper
        const buttonWrapper = document.createElement('div');
        buttonWrapper.className = 'product-detail-get-quote';
        buttonWrapper.style.cssText = 'margin-top: 16px; width: 100%;';
        
        // Create the button
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'product-detail-get-quote-btn';
        btn.textContent = 'Get a Quote';
        btn.style.cssText = [
            'padding: 12px 24px',
            'background: #000',
            'color: #fff',
            'border: none',
            'cursor: pointer',
            'width: 100%',
            'font-size: 16px',
            'font-weight: 600',
            'border-radius: 4px',
            'transition: background 0.3s ease'
        ].join(';');
        
        btn.setAttribute('data-quote-bound', '1');
        btn.setAttribute('data-product-handle', handle);
        
        // Add hover effect
        btn.addEventListener('mouseenter', () => {
            btn.style.background = '#333';
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.background = '#000';
        });
        
        // Block early events
        const blockEarly = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };
        ['mousedown', 'mouseup', 'touchstart', 'touchend'].forEach(evt =>
            btn.addEventListener(evt, blockEarly, { capture: true })
        );
        
        // Handle click
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const product = await fetchProductByHandle(handle);
            if (!product) {
                return showToast('Unable to load product details', 'red');
            }
            openQuoteModalWithProduct(product);
        });
        
        buttonWrapper.appendChild(btn);
        
        // Try to insert after price or form
        const priceElement = targetContainer.querySelector('product-price, .price, .product__price');
        const divider = targetContainer.querySelector('.divider');
        const formElement = targetContainer.querySelector('form[action*="/cart/add"]');
        
        if (divider && divider.parentNode) {
            // Insert after the divider
            divider.insertAdjacentElement('afterend', buttonWrapper);
        } else if (priceElement && priceElement.parentNode) {
            // Insert after price
            priceElement.insertAdjacentElement('afterend', buttonWrapper);
        } else if (formElement && formElement.parentNode) {
            // Insert after form
            formElement.insertAdjacentElement('afterend', buttonWrapper);
        } else {
            // Append to container as fallback
            targetContainer.appendChild(buttonWrapper);
        }
        
        console.log('Product page quote button injected successfully');
    }


    // ---------------------------
    // INIT
    // ---------------------------
    onDomReady(() => {
        loadExternalCSS();
        bindExistingGetQuoteButtons(); // <- binds your theme's .get_quote_btn
        injectButtonsIntoGrid(); // <- inject buttons under product cards
        // observeDynamic();
        injectButtonOnProductPage();
        observeDynamicGrids();
    });
})();