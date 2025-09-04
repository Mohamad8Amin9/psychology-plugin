document.addEventListener("DOMContentLoaded", function () {
    let qIndex = 0;
    
    // مدیریت تب‌های اصلی
    function initializeMainTabs() {
        const mainTabs = document.querySelectorAll('.tab-nav li');
        const mainContents = document.querySelectorAll('.tab-content');
        
        mainTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.dataset.tab;
                
                // حذف کلاس active از همه تب‌ها
                mainTabs.forEach(t => t.classList.remove('active'));
                mainContents.forEach(c => c.classList.remove('active'));
                
                // اضافه کردن کلاس active به تب انتخاب شده
                this.classList.add('active');
                const targetContent = document.getElementById(targetTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                    
                    // اگر تب نتایج انتخاب شد، سیستم نتایج را راه‌اندازی کن
                    if (targetTab === 'results-tab') {
                        setTimeout(() => {
                            initializeResultsSystem();
                        }, 100);
                    }
                }
            });
        });
    }
    
    // راه‌اندازی تب‌های اصلی
    initializeMainTabs();

    function updateQuestionNumbers() {
        document.querySelectorAll(".question-group").forEach((group, qIndex) => {
            group.setAttribute("data-q", qIndex);
            group.querySelector("h4").textContent = "سوال " + (qIndex + 1);

            const questionInput = group.querySelector('input[name^="questions["][name$="[text]"]');
            if (questionInput) {
                questionInput.name = `questions[${qIndex}][text]`;
            }

            const requiredCheckbox = group.querySelector('input[name$="[required]"]');
            if (requiredCheckbox) {
                requiredCheckbox.name = `questions[${qIndex}][required]`;
            }

            group.querySelectorAll(".answer-row").forEach((answerRow, aIndex) => {
                const answerText = answerRow.querySelector('input[name$="[text]"]');
                const answerLetter = answerRow.querySelector('input[name$="[letter]"]');
                const answerScore = answerRow.querySelector('input[name$="[score]"]');
                if (answerText) answerText.name = `questions[${qIndex}][answers][${aIndex}][text]`;
                if (answerLetter) answerLetter.name = `questions[${qIndex}][answers][${aIndex}][letter]`;
                if (answerScore) answerScore.name = `questions[${qIndex}][answers][${aIndex}][score]`;
            });
        });
        qIndex = document.querySelectorAll(".question-group").length;
    }

    // راه‌اندازی اولیه شماره‌گذاری سوالات موجود
    updateQuestionNumbers();

    document.getElementById("add-question").addEventListener("click", function () {
        const container = document.getElementById("questions-container");
        const requiredMode = document.getElementById("psychology_test_required_mode").value;
        const isCustomMode = requiredMode === 'custom';
        const html = 
            `<div class="question-group" data-q="${qIndex}">
                <h4>سوال ${qIndex + 1}</h4>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <input type="text" name="questions[${qIndex}][text]" placeholder="متن سوال" style="flex:1;" />
                    <label style="display:flex;align-items:center;gap:5px;font-size:12px;color:#666;">
                        <input type="checkbox" name="questions[${qIndex}][required]" value="1" class="question-required-checkbox" ${isCustomMode ? '' : 'disabled'}>
                        ضروری
                    </label>
                </div>
                <div class="answers-container"></div>
                <button type="button" class="add-answer">افزودن پاسخ</button>
                <button type="button" class="remove-question"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="18" height="18"><path d="M3.726563 3.023438L3.023438 3.726563L7.292969 8L3.023438 12.269531L3.726563 12.980469L8 8.707031L12.269531 12.980469L12.980469 12.269531L8.707031 8L12.980469 3.726563L12.269531 3.023438L8 7.292969Z" /></svg></button>
                <button type="button" class="duplicate-question">📄 کپی سوال</button>
            </div>`;
        container.insertAdjacentHTML("beforeend", html);
        qIndex++;
        updateQuestionNumbers();
    });

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("add-answer")) {
            updateQuestionNumbers();
        }
        if (e.target.classList.contains("remove-answer") || e.target.closest(".remove-answer")) {
            updateQuestionNumbers();
        }
        if (e.target.classList.contains("duplicate-question")) {
            updateQuestionNumbers();
        }
        if (e.target.classList.contains("remove-question") || e.target.closest(".remove-question")) {
            updateQuestionNumbers();
        }
    });

    // تابع تغییر حالت اجباری بودن سوالات
    window.toggleRequiredOptions = function() {
        const requiredMode = document.getElementById("psychology_test_required_mode").value;
        const checkboxes = document.querySelectorAll(".question-required-checkbox");
        
        checkboxes.forEach(checkbox => {
            if (requiredMode === 'custom') {
                checkbox.disabled = false;
            } else if (requiredMode === 'required') {
                checkbox.disabled = true;
                checkbox.checked = true;
            } else { // optional
                checkbox.disabled = true;
                checkbox.checked = false;
            }
        });
    };

    // اجرای تابع در بارگذاری صفحه
    if (document.getElementById("psychology_test_required_mode")) {
        toggleRequiredOptions();
    }

    // سیستم نتایج آزمون - راه‌اندازی با تاخیر و retry
    function initResultsWithRetry() {
        const resultTabs = document.querySelectorAll('.results-sub-tabs .sub-tab-nav li');
        if (resultTabs.length > 0) {
            initializeResultsSystem();
        } else {
            setTimeout(initResultsWithRetry, 200);
        }
    }
    
    // شروع راه‌اندازی
    initResultsWithRetry();

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("add-answer")) {
            const group = e.target.closest(".question-group");
            const q = group.dataset.q;
            const answers = group.querySelector(".answers-container");
            const index = answers.children.length;
            const html = 
                `<div class="answer-row">
                    <input type="text" name="questions[${q}][answers][${index}][text]" placeholder="پاسخ" />
                    <input type="text" name="questions[${q}][answers][${index}][letter]" placeholder="حرف" />
                    <input type="number" name="questions[${q}][answers][${index}][score]" placeholder="امتیاز" />
                    <button type="button" class="remove-answer"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="18" height="18"><path d="M3.726563 3.023438L3.023438 3.726563L7.292969 8L3.023438 12.269531L3.726563 12.980469L8 8.707031L12.269531 12.980469L12.980469 12.269531L8.707031 8L12.980469 3.726563L12.269531 3.023438L8 7.292969Z" fill="#FFFFFF" /></svg></button>
                </div>`;
            answers.insertAdjacentHTML("beforeend", html);
        }

        if (e.target.classList.contains("remove-answer") || e.target.closest(".remove-answer")) {
            e.preventDefault();
            e.stopPropagation();
            document.activeElement.blur(); // رفع فوکوس از input قبل از حذف
            const row = e.target.closest(".answer-row");
            if (row) {
                row.remove();
            }
        }

        if (e.target.classList.contains("duplicate-question")) {
            const group = e.target.closest(".question-group");
            const clone = group.cloneNode(true);
            qIndex++;
            clone.setAttribute("data-q", qIndex);
            clone.querySelector("h4").textContent = "سوال " + (qIndex + 1);

            
            clone.querySelectorAll("input").forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/questions\[\d+\]/, "questions[" + qIndex + "]");
                }
            });

            
            group.insertAdjacentElement("afterend", clone);
            updateQuestionNumbers();
        }

        if (e.target.classList.contains("remove-question") || e.target.closest(".remove-question")) {
            e.target.closest(".question-group").remove();
            updateQuestionNumbers();
        }

        if (e.target.matches(".tab-nav li")) {
            document.querySelectorAll(".tab-nav li").forEach(li => li.classList.remove("active"));
            document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
            e.target.classList.add("active");
            document.getElementById(e.target.dataset.tab).classList.add("active");
        }

        // Sub-tab functionality
        if (e.target.matches(".sub-tab-nav li")) {
            const subTabContainer = e.target.closest(".style-sub-tabs");
            if (subTabContainer) {
                subTabContainer.querySelectorAll(".sub-tab-nav li").forEach(li => li.classList.remove("active"));
                subTabContainer.querySelectorAll(".sub-tab-content").forEach(tab => tab.classList.remove("active"));
                e.target.classList.add("active");
                const targetSubTab = document.getElementById(e.target.dataset.subTab);
                if (targetSubTab) {
                    targetSubTab.classList.add("active");
                }
            }
        }
    });

    // Font preview functionality
    const fontUrlInput = document.getElementById('psychology_test_custom_font');
    const fontFamilyInput = document.getElementById('psychology_test_font_family');
    const fontWeightInput = document.getElementById('psychology_test_font_weight');
    const fontPreview = document.getElementById('font-preview');

    function updateFontPreview() {
        if (!fontPreview) return;
        
        const fontUrl = fontUrlInput ? fontUrlInput.value : '';
        const fontFamily = fontFamilyInput ? fontFamilyInput.value : 'inherit';
        const fontWeight = fontWeightInput ? fontWeightInput.value : 'normal';
        
        // Remove existing font elements
        const existingLink = document.querySelector('link[data-custom-font]');
        const existingStyle = document.querySelector('style[data-custom-font]');
        if (existingLink) existingLink.remove();
        if (existingStyle) existingStyle.remove();
        
        // Add font if URL is provided
        if (fontUrl) {
            // Check if it's a font file (TTF, OTF, WOFF, WOFF2)
            const fontFileRegex = /\.(ttf|otf|woff|woff2)$/i;
            
            if (fontFileRegex.test(fontUrl)) {
                // Create @font-face rule for font files
                const style = document.createElement('style');
                style.setAttribute('data-custom-font', 'true');
                
                const ext = fontUrl.split('.').pop().toLowerCase();
                let format = 'truetype';
                if (ext === 'otf') format = 'opentype';
                else if (ext === 'woff') format = 'woff';
                else if (ext === 'woff2') format = 'woff2';
                
                style.textContent = `
                    @font-face {
                        font-family: '${fontFamily}';
                        src: url('${fontUrl}') format('${format}');
                        font-weight: ${fontWeight};
                        font-style: normal;
                        font-display: swap;
                    }
                `;
                document.head.appendChild(style);
            } else {
                // Load CSS file
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = fontUrl;
                link.setAttribute('data-custom-font', 'true');
                document.head.appendChild(link);
            }
        }
        
        // Update preview font family and weight
        fontPreview.style.fontFamily = fontFamily === 'inherit' ? 'inherit' : `'${fontFamily}', sans-serif`;
        fontPreview.style.fontWeight = fontWeight;
    }

    // Add event listeners for font inputs
    if (fontUrlInput) {
        fontUrlInput.addEventListener('input', updateFontPreview);
    }
    
    if (fontFamilyInput) {
        fontFamilyInput.addEventListener('input', updateFontPreview);
    }
    
    if (fontWeightInput) {
        fontWeightInput.addEventListener('change', updateFontPreview);
    }

    // Color Panel System (Elementor Style)
    let activeColorPanel = null;
    const colorPreview = document.getElementById('color-preview');

    // Create color panel HTML
    function createColorPanel() {
        const panelHTML = `
            <div id="color-panel-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:none;">
                <div id="color-panel" style="position:absolute;background:white;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,0.3);padding:20px;min-width:280px;max-width:320px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                        <h4 style="margin:0;font-size:16px;" id="panel-title">تنظیم رنگ</h4>
                        <button onclick="closeColorPanel()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#666;">×</button>
                    </div>
                    
                    <div style="margin-bottom:15px;">
                        <div style="display:flex;gap:10px;margin-bottom:10px;">
                            <button type="button" class="panel-type-btn solid-btn" data-type="solid" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;background:white;cursor:pointer;font-size:12px;">Solid</button>
                            <button type="button" class="panel-type-btn gradient-btn" data-type="gradient" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;background:white;cursor:pointer;font-size:12px;">Gradient</button>
                        </div>
                    </div>
                    
                    <div id="solid-controls" style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;font-size:12px;color:#666;">رنگ اصلی:</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="panel-primary-color" style="width:50px;height:35px;border:none;border-radius:4px;cursor:pointer;">
                            <input type="text" id="panel-primary-text" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;font-size:12px;" placeholder="#0d6efd">
                        </div>
                    </div>
                    
                    <div id="gradient-controls" style="margin-bottom:15px;display:none;">
                        <label style="display:block;margin-bottom:5px;font-size:12px;color:#666;">رنگ اول:</label>
                        <div style="display:flex;gap:10px;align-items:center;margin-bottom:10px;">
                            <input type="color" id="panel-gradient-color1" style="width:50px;height:35px;border:none;border-radius:4px;cursor:pointer;">
                            <input type="text" id="panel-gradient-text1" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;font-size:12px;" placeholder="#0d6efd">
                        </div>
                        <label style="display:block;margin-bottom:5px;font-size:12px;color:#666;">رنگ دوم:</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="panel-gradient-color2" style="width:50px;height:35px;border:none;border-radius:4px;cursor:pointer;">
                            <input type="text" id="panel-gradient-text2" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;font-size:12px;" placeholder="#0dcaf0">
                        </div>
                    </div>
                    
                    <div style="display:flex;gap:10px;">
                        <button onclick="applyColorPanel()" style="flex:1;padding:10px;background:#007cba;color:white;border:none;border-radius:4px;cursor:pointer;font-size:14px;">اعمال</button>
                        <button onclick="closeColorPanel()" style="flex:1;padding:10px;background:#f8f9fa;color:#333;border:1px solid #ddd;border-radius:4px;cursor:pointer;font-size:14px;">انصراف</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', panelHTML);
    }

    // Open color panel - Make it globally accessible
    window.openColorPanel = function(colorType) {
        activeColorPanel = colorType;
        const overlay = document.getElementById('color-panel-overlay');
        const panel = document.getElementById('color-panel');
        const title = document.getElementById('panel-title');
        
        // Set title
        const titles = {
            'primary': 'رنگ اصلی',
            'hover': 'رنگ هاور',
            'text': 'رنگ متن',
            'background': 'رنگ پس‌زمینه'
        };
        title.textContent = titles[colorType] || 'تنظیم رنگ';
        
        // Load current values
        loadColorPanelValues(colorType);
        
        // Position panel near the clicked element
        const clickedElement = document.querySelector(`[data-color-type="${colorType}"] .color-preview`);
        if (clickedElement) {
            const rect = clickedElement.getBoundingClientRect();
            panel.style.top = (rect.bottom + 10) + 'px';
            panel.style.left = rect.left + 'px';
        }
        
        overlay.style.display = 'block';
        
        // Add event listeners
        addColorPanelEventListeners();
    };

    // Close color panel - Make it globally accessible
    window.closeColorPanel = function() {
        const overlay = document.getElementById('color-panel-overlay');
        overlay.style.display = 'none';
        activeColorPanel = null;
    };

    // Load current values into panel
    function loadColorPanelValues(colorType) {
        const primaryColor = document.getElementById(`psychology_test_${colorType}_color`).value;
        const colorTypeValue = document.getElementById(`psychology_test_${colorType}_type`).value;
        const secondaryColor = document.getElementById(`psychology_test_${colorType}_secondary`).value;
        
        // Set type buttons
        document.querySelectorAll('.panel-type-btn').forEach(btn => {
            btn.style.background = btn.dataset.type === colorTypeValue ? '#007cba' : 'white';
            btn.style.color = btn.dataset.type === colorTypeValue ? 'white' : '#333';
        });
        
        // Show/hide controls
        document.getElementById('solid-controls').style.display = colorTypeValue === 'solid' ? 'block' : 'none';
        document.getElementById('gradient-controls').style.display = colorTypeValue === 'gradient' ? 'block' : 'none';
        
        // Set color values
        document.getElementById('panel-primary-color').value = primaryColor;
        document.getElementById('panel-primary-text').value = primaryColor;
        document.getElementById('panel-gradient-color1').value = primaryColor;
        document.getElementById('panel-gradient-text1').value = primaryColor;
        document.getElementById('panel-gradient-color2').value = secondaryColor;
        document.getElementById('panel-gradient-text2').value = secondaryColor;
    }

    // Apply color panel changes - Make it globally accessible
    window.applyColorPanel = function() {
        if (!activeColorPanel) return;
        
        const colorType = document.querySelector('.panel-type-btn[style*="background: rgb(0, 124, 186)"]').dataset.type;
        const primaryColor = document.getElementById('panel-primary-color').value;
        const secondaryColor = document.getElementById('panel-gradient-color2').value;
        
        // Update hidden inputs
        document.getElementById(`psychology_test_${activeColorPanel}_color`).value = primaryColor;
        document.getElementById(`psychology_test_${activeColorPanel}_type`).value = colorType;
        document.getElementById(`psychology_test_${activeColorPanel}_secondary`).value = secondaryColor;
        
        // Update preview
        const previewElement = document.querySelector(`[data-color-type="${activeColorPanel}"] .color-preview`);
        if (previewElement) {
            if (colorType === 'gradient') {
                previewElement.style.background = `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`;
            } else {
                previewElement.style.background = primaryColor;
            }
        }
        
        // Update main preview
        updateColorPreview();
        
        closeColorPanel();
    };

    // Add event listeners to color panel
    function addColorPanelEventListeners() {
        // Type buttons
        document.querySelectorAll('.panel-type-btn').forEach(btn => {
            btn.onclick = function() {
                const type = this.dataset.type;
                document.querySelectorAll('.panel-type-btn').forEach(b => {
                    b.style.background = 'white';
                    b.style.color = '#333';
                });
                this.style.background = '#007cba';
                this.style.color = 'white';
                
                document.getElementById('solid-controls').style.display = type === 'solid' ? 'block' : 'none';
                document.getElementById('gradient-controls').style.display = type === 'gradient' ? 'block' : 'none';
            };
        });
        
        // Color picker sync
        const colorInputs = [
            { picker: 'panel-primary-color', text: 'panel-primary-text' },
            { picker: 'panel-gradient-color1', text: 'panel-gradient-text1' },
            { picker: 'panel-gradient-color2', text: 'panel-gradient-text2' }
        ];
        
        colorInputs.forEach(input => {
            const picker = document.getElementById(input.picker);
            const text = document.getElementById(input.text);
            
            picker.oninput = function() {
                text.value = this.value;
            };
            
            text.oninput = function() {
                picker.value = this.value;
            };
        });
    }

    // Update main color preview
    function updateColorPreview() {
        if (!colorPreview) return;
        
        const primaryColor = document.getElementById('psychology_test_primary_color').value;
        const primaryType = document.getElementById('psychology_test_primary_type').value;
        const primarySecondary = document.getElementById('psychology_test_primary_secondary').value;
        const textColor = document.getElementById('psychology_test_text_color').value;
        const backgroundColor = document.getElementById('psychology_test_background_color').value;
        
        // Update preview background and text color
        colorPreview.style.background = backgroundColor;
        colorPreview.style.color = textColor;
        
        // Update preview title color
        const previewTitle = colorPreview.querySelector('h4');
        if (previewTitle) {
            previewTitle.style.color = primaryColor;
        }
        
        // Update preview button
        const previewButton = colorPreview.querySelector('#preview-button');
        if (previewButton) {
            if (primaryType === 'gradient') {
                previewButton.style.background = `linear-gradient(135deg, ${primaryColor} 0%, ${primarySecondary} 100%)`;
            } else {
                previewButton.style.background = primaryColor;
            }
        }
    }

    // Initialize color panel system
    createColorPanel();
    
    // Add click event listeners to color previews
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('color-preview')) {
            const colorType = e.target.closest('.color-panel').dataset.colorType;
            openColorPanel(colorType);
        }
    });
    
    // Close panel when clicking outside
    document.addEventListener('click', function(e) {
        const overlay = document.getElementById('color-panel-overlay');
        if (overlay && e.target === overlay) {
            closeColorPanel();
        }
    });

    // سیستم نتایج آزمون
    function initializeResultsSystem() {
        console.log('Initializing Results System...');
        
        // مدیریت تب‌های نتایج
        const resultTabs = document.querySelectorAll('.results-sub-tabs .sub-tab-nav li');
        const resultContents = document.querySelectorAll('.results-sub-tabs .sub-tab-content');
        
        console.log('Found result tabs:', resultTabs.length);
        console.log('Found result contents:', resultContents.length);
        
        if (resultTabs.length === 0) {
            console.warn('No result tabs found. Retrying in 500ms...');
            setTimeout(initializeResultsSystem, 500);
            return;
        }
        
        resultTabs.forEach(tab => {
            // حذف event listener های قبلی
            tab.removeEventListener('click', tab.clickHandler);
            
            // تعریف event handler جدید
            tab.clickHandler = function(e) {
                e.preventDefault();
                e.stopPropagation();
                const targetTab = this.dataset.subTab;
                console.log('Clicked tab:', targetTab);
                
                // حذف کلاس active از همه تب‌ها
                resultTabs.forEach(t => t.classList.remove('active'));
                resultContents.forEach(c => c.classList.remove('active'));
                
                // اضافه کردن کلاس active به تب انتخاب شده
                this.classList.add('active');
                const targetContent = document.getElementById(targetTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                    console.log('Activated tab:', targetTab);
                } else {
                    console.error('Target content not found:', targetTab);
                }
            };
            
            // اضافه کردن event listener
            tab.addEventListener('click', tab.clickHandler);
        });

        // مدیریت نوع محاسبه
        const calculationType = document.getElementById('psychology_test_calculation_type');
        const customFormulaSection = document.getElementById('custom-formula-section');
        
        if (calculationType) {
            calculationType.addEventListener('change', function() {
                if (this.value === 'custom_formula') {
                    customFormulaSection.style.display = 'block';
                } else {
                    customFormulaSection.style.display = 'none';
                }
            });
        }

        // افزودن شرط جدید
        const addConditionBtn = document.getElementById('add-result-condition');
        if (addConditionBtn) {
            addConditionBtn.addEventListener('click', function() {
                addNewCondition();
            });
        }

        // حذف شرط
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-condition')) {
                e.target.closest('.result-condition').remove();
                updateConditionNumbers();
            }
        });

        // اعمال قالب‌های آماده
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('apply-template')) {
                const template = e.target.dataset.template;
                applyTemplate(template);
            }
        });
        
        // اضافه کردن event listener برای تب اصلی نتایج
        const resultsTab = document.querySelector('[data-tab="results-tab"]');
        if (resultsTab) {
            resultsTab.addEventListener('click', function() {
                // تاخیر کوتاه برای اطمینان از نمایش تب نتایج
                setTimeout(() => {
                    initializeResultsSystem();
                }, 100);
            });
        }
        
        // اضافه کردن event listener برای کلیک روی هر تب اصلی
        document.addEventListener('click', function(e) {
            if (e.target.matches('.tab-nav li')) {
                const targetTab = e.target.dataset.tab;
                if (targetTab === 'results-tab') {
                    setTimeout(() => {
                        initializeResultsSystem();
                    }, 200);
                }
            }
        });
        
        console.log('Results system initialization completed');
        
        // تست عملکرد تب‌ها
        setTimeout(() => {
            const resultTabs = document.querySelectorAll('.results-sub-tabs .sub-tab-nav li');
            const resultContents = document.querySelectorAll('.results-sub-tabs .sub-tab-content');
            console.log('Final check - Result tabs:', resultTabs.length, 'Result contents:', resultContents.length);
            
            resultTabs.forEach((tab, index) => {
                console.log(`Tab ${index}:`, tab.textContent, 'data-sub-tab:', tab.dataset.subTab);
            });
            
            resultContents.forEach((content, index) => {
                console.log(`Content ${index}:`, content.id);
            });
        }, 200);
    }

    // افزودن شرط جدید
    function addNewCondition() {
        const container = document.getElementById('result-conditions-container');
        const conditionIndex = container.children.length;
        
        const conditionHTML = `
            <div class="result-condition" data-condition="${conditionIndex}" style="border:1px solid #ddd;padding:15px;margin-bottom:15px;border-radius:8px;background:#f9f9f9;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                    <h4 style="margin:0;">شرط ${conditionIndex + 1}</h4>
                    <button type="button" class="remove-condition" style="background:#dc3545;color:white;border:none;padding:5px 10px;border-radius:4px;cursor:pointer;">حذف</button>
                </div>
                
                <div class="condition-settings">
                    <div class="setting-row">
                        <label>نوع شرط:</label>
                        <select name="psychology_test_results[conditions][${conditionIndex}][type]" style="width:150px;">
                            <option value="score_range">بازه امتیاز</option>
                            <option value="percentage">درصد</option>
                            <option value="letter_count">تعداد حروف</option>
                            <option value="custom">سفارشی</option>
                        </select>
                    </div>
                    
                    <div class="setting-row">
                        <label>شرط:</label>
                        <select name="psychology_test_results[conditions][${conditionIndex}][operator]" style="width:100px;">
                            <option value=">=">>=</option>
                            <option value=">">></option>
                            <option value="<="><=</option>
                            <option value="<"><</option>
                            <option value="==">==</option>
                            <option value="!=">!=</option>
                            <option value="between">بین</option>
                        </select>
                        <input type="number" name="psychology_test_results[conditions][${conditionIndex}][value1]" placeholder="مقدار 1" style="width:100px;">
                        <input type="number" name="psychology_test_results[conditions][${conditionIndex}][value2]" placeholder="مقدار 2" style="width:100px;">
                    </div>
                    
                    <div class="setting-row">
                        <label>عنوان نتیجه:</label>
                        <input type="text" name="psychology_test_results[conditions][${conditionIndex}][title]" placeholder="مثال: شخصیت برون‌گرا" style="width:300px;">
                    </div>
                    
                    <div class="setting-row">
                        <label>توضیحات:</label>
                        <textarea name="psychology_test_results[conditions][${conditionIndex}][description]" rows="3" style="width:100%;" placeholder="توضیحات کامل نتیجه..."></textarea>
                    </div>
                    
                    <div class="setting-row">
                        <label>رنگ نتیجه:</label>
                        <input type="color" name="psychology_test_results[conditions][${conditionIndex}][color]" value="#0073aa" style="width:60px;">
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', conditionHTML);
    }

    // بروزرسانی شماره شرط‌ها
    function updateConditionNumbers() {
        const conditions = document.querySelectorAll('.result-condition');
        conditions.forEach((condition, index) => {
            condition.dataset.condition = index;
            condition.querySelector('h4').textContent = `شرط ${index + 1}`;
            
            // بروزرسانی نام فیلدها
            const inputs = condition.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                const name = input.name;
                if (name) {
                    input.name = name.replace(/\[\d+\]/, `[${index}]`);
                }
            });
        });
    }

    // اعمال قالب‌های آماده
    function applyTemplate(template) {
        const calculationType = document.getElementById('psychology_test_calculation_type');
        const container = document.getElementById('result-conditions-container');
        
        // پاک کردن شرط‌های موجود
        container.innerHTML = '';
        
        switch (template) {
            case 'mbti':
                calculationType.value = 'mbti_style';
                addMBTIConditions();
                break;
            case 'big_five':
                calculationType.value = 'big_five';
                addBigFiveConditions();
                break;
            case 'iq_test':
                calculationType.value = 'percentage';
                addIQTestConditions();
                break;
            case 'personality':
                calculationType.value = 'simple_sum';
                addPersonalityConditions();
                break;
        }
        
        // نمایش/مخفی کردن بخش فرمول سفارشی
        const customFormulaSection = document.getElementById('custom-formula-section');
        customFormulaSection.style.display = 'none';
    }

    // قالب MBTI
    function addMBTIConditions() {
        const mbtiTypes = [
            { type: 'ISTJ', title: 'ISTJ - محافظ', description: 'شما فردی مسئولیت‌پذیر، عملی و منظم هستید.' },
            { type: 'ISFJ', title: 'ISFJ - مدافع', description: 'شما فردی دلسوز، وفادار و سنتی هستید.' },
            { type: 'INFJ', title: 'INFJ - مشاور', description: 'شما فردی آرمان‌گرا، خلاق و همدل هستید.' },
            { type: 'INTJ', title: 'INTJ - معمار', description: 'شما فردی استراتژیک، مستقل و تحلیل‌گر هستید.' },
            { type: 'ISTP', title: 'ISTP - متخصص', description: 'شما فردی انعطاف‌پذیر، منطقی و عمل‌گرا هستید.' },
            { type: 'ISFP', title: 'ISFP - ماجراجو', description: 'شما فردی هنرمند، صلح‌طلب و وفادار هستید.' },
            { type: 'INFP', title: 'INFP - میانجی', description: 'شما فردی ایده‌آلیست، خلاق و همدل هستید.' },
            { type: 'INTP', title: 'INTP - متفکر', description: 'شما فردی نوآور، منطقی و مستقل هستید.' },
            { type: 'ESTP', title: 'ESTP - کارآفرین', description: 'شما فردی عمل‌گرا، انعطاف‌پذیر و ریسک‌پذیر هستید.' },
            { type: 'ESFP', title: 'ESFP - سرگرم‌کننده', description: 'شما فردی اجتماعی، خوش‌بین و دوستانه هستید.' },
            { type: 'ENFP', title: 'ENFP - مبارز', description: 'شما فردی خلاق، مشتاق و مستقل هستید.' },
            { type: 'ENTP', title: 'ENTP - نوآور', description: 'شما فردی باهوش، کنجکاو و استراتژیک هستید.' },
            { type: 'ESTJ', title: 'ESTJ - مدیر', description: 'شما فردی منظم، مسئولیت‌پذیر و عمل‌گرا هستید.' },
            { type: 'ESFJ', title: 'ESFJ - کنسول', description: 'شما فردی اجتماعی، مسئولیت‌پذیر و دلسوز هستید.' },
            { type: 'ENFJ', title: 'ENFJ - رهبر', description: 'شما فردی کاریزماتیک، الهام‌بخش و همدل هستید.' },
            { type: 'ENTJ', title: 'ENTJ - فرمانده', description: 'شما فردی جسور، استراتژیک و رهبر هستید.' }
        ];
        
        mbtiTypes.forEach((mbti, index) => {
            addNewCondition();
            const condition = document.querySelector(`[data-condition="${index}"]`);
            const titleInput = condition.querySelector('input[name*="[title]"]');
            const descInput = condition.querySelector('textarea[name*="[description]"]');
            
            titleInput.value = mbti.title;
            descInput.value = mbti.description;
        });
    }

    // قالب Big Five
    function addBigFiveConditions() {
        const bigFiveFactors = [
            { factor: 'O', title: 'باز بودن به تجربه', description: 'شما فردی خلاق، کنجکاو و پذیرای ایده‌های جدید هستید.' },
            { factor: 'C', title: 'وجدان‌مندی', description: 'شما فردی منظم، مسئولیت‌پذیر و هدف‌مند هستید.' },
            { factor: 'E', title: 'برون‌گرایی', description: 'شما فردی اجتماعی، پرانرژی و مثبت‌اندیش هستید.' },
            { factor: 'A', title: 'توافق‌پذیری', description: 'شما فردی همدل، مهربان و قابل اعتماد هستید.' },
            { factor: 'N', title: 'روان‌رنجوری', description: 'شما فردی حساس، نگران و عاطفی هستید.' }
        ];
        
        bigFiveFactors.forEach((factor, index) => {
            addNewCondition();
            const condition = document.querySelector(`[data-condition="${index}"]`);
            const titleInput = condition.querySelector('input[name*="[title]"]');
            const descInput = condition.querySelector('textarea[name*="[description]"]');
            
            titleInput.value = factor.title;
            descInput.value = factor.description;
        });
    }

    // قالب تست هوش
    function addIQTestConditions() {
        const iqRanges = [
            { range: '130+', title: 'بسیار باهوش', description: 'شما دارای هوش بسیار بالایی هستید.' },
            { range: '120-129', title: 'باهوش', description: 'شما دارای هوش بالایی هستید.' },
            { range: '110-119', title: 'بالاتر از متوسط', description: 'شما دارای هوش بالاتر از متوسط هستید.' },
            { range: '90-109', title: 'متوسط', description: 'شما دارای هوش متوسط هستید.' },
            { range: '80-89', title: 'پایین‌تر از متوسط', description: 'شما دارای هوش پایین‌تر از متوسط هستید.' },
            { range: '70-79', title: 'مرزی', description: 'شما در مرز هوش متوسط قرار دارید.' },
            { range: '<70', title: 'نیاز به توجه ویژه', description: 'شما نیاز به توجه و حمایت ویژه دارید.' }
        ];
        
        iqRanges.forEach((range, index) => {
            addNewCondition();
            const condition = document.querySelector(`[data-condition="${index}"]`);
            const titleInput = condition.querySelector('input[name*="[title]"]');
            const descInput = condition.querySelector('textarea[name*="[description]"]');
            
            titleInput.value = range.title;
            descInput.value = range.description;
        });
    }

    // قالب شخصیت عمومی
    function addPersonalityConditions() {
        const personalityTypes = [
            { type: 'A', title: 'شخصیت نوع A', description: 'شما فردی رقابتی، عجول و پرانرژی هستید.' },
            { type: 'B', title: 'شخصیت نوع B', description: 'شما فردی آرام، صبور و غیررقابتی هستید.' },
            { type: 'C', title: 'شخصیت نوع C', description: 'شما فردی دقیق، محتاط و منطقی هستید.' },
            { type: 'D', title: 'شخصیت نوع D', description: 'شما فردی نگران، منفی‌نگر و گوشه‌گیر هستید.' }
        ];
        
        personalityTypes.forEach((type, index) => {
            addNewCondition();
            const condition = document.querySelector(`[data-condition="${index}"]`);
            const titleInput = condition.querySelector('input[name*="[title]"]');
            const descInput = condition.querySelector('textarea[name*="[description]"]');
            
            titleInput.value = type.title;
            descInput.value = type.description;
        });
    }

    // پیش‌نمایش قالب سوالات
    function initializeQuestionThemePreview() {
        const themeSelect = document.getElementById('psychology_test_question_theme');
        const defaultPreview = document.getElementById('default-theme-preview');
        const bigFivePreview = document.getElementById('big-five-theme-preview');

        if (themeSelect && defaultPreview && bigFivePreview) {
            // تنظیم پیش‌نمایش اولیه
            updateThemePreview();

            // تغییر پیش‌نمایش با تغییر انتخاب
            themeSelect.addEventListener('change', updateThemePreview);

            function updateThemePreview() {
                const selectedTheme = themeSelect.value;
                
                // مخفی کردن همه پیش‌نمایش‌ها
                defaultPreview.style.display = 'none';
                bigFivePreview.style.display = 'none';

                // نمایش فقط قالب انتخاب شده
                if (selectedTheme === 'default') {
                    defaultPreview.style.display = 'block';
                    defaultPreview.style.border = '2px solid #0073aa';
                    defaultPreview.style.background = '#e3f2fd';
                    defaultPreview.querySelector('h5').style.color = '#0073aa';
                } else if (selectedTheme === 'big_five') {
                    bigFivePreview.style.display = 'block';
                    bigFivePreview.style.border = '2px solid #0073aa';
                    bigFivePreview.style.background = '#e3f2fd';
                    bigFivePreview.querySelector('h5').style.color = '#0073aa';
                }
            }
        }
    }

    // فراخوانی تابع پیش‌نمایش قالب سوالات
    initializeQuestionThemePreview();
    
    // Import/Export functionality
    initializeImportExport();
    
    function initializeImportExport() {
        const importType = document.getElementById('import_type');
        const importMethods = document.querySelectorAll('.import-method');
        const importBtn = document.getElementById('import_questions');
        const previewBtn = document.getElementById('preview_import');
        const exportBtn = document.getElementById('export_questions');
        const confirmBtn = document.getElementById('confirm_import');
        const cancelBtn = document.getElementById('cancel_import');
        
        // Toggle import methods
        if (importType) {
            importType.addEventListener('change', function() {
                importMethods.forEach(method => method.style.display = 'none');
                const selectedMethod = document.getElementById(this.value + '_import');
                if (selectedMethod) {
                    selectedMethod.style.display = 'block';
                }
            });
        }
        
        // Preview import
        if (previewBtn) {
            previewBtn.addEventListener('click', function() {
                const importType = document.getElementById('import_type').value;
                let questions = [];
                
                if (importType === 'mbti_text') {
                    const text = document.getElementById('mbti_questions_text').value;
                    questions = parseMBTIText(text);
                }
                
                if (questions.length > 0) {
                    showImportPreview(questions);
                } else {
                    alert('هیچ سوالی یافت نشد. لطفاً متن را بررسی کنید.');
                }
            });
        }

        // Import questions directly
        if (importBtn) {
            importBtn.addEventListener('click', function() {
                console.log('Import button clicked');
                const importType = document.getElementById('import_type').value;
                let questions = [];
                
                if (importType === 'mbti_text') {
                    const text = document.getElementById('mbti_questions_text').value;
                    console.log('Text to parse:', text);
                    questions = parseMBTIText(text);
                    console.log('Parsed questions:', questions);
                }
                
                if (questions.length > 0) {
                    importQuestions(questions);
                } else {
                    alert('هیچ سوالی یافت نشد. لطفاً متن را بررسی کنید.');
                }
            });
        }
        
        // Confirm import
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                const importType = document.getElementById('import_type').value;
                let questions = [];
                
                if (importType === 'mbti_text') {
                    const text = document.getElementById('mbti_questions_text').value;
                    questions = parseMBTIText(text);
                }
                
                if (questions.length > 0) {
                    importQuestions(questions);
                }
            });
        }
        
        // Cancel import
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                document.getElementById('import_preview').style.display = 'none';
            });
        }
        
        // Export questions
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                const format = document.getElementById('export_format').value;
                exportQuestions(format);
            });
        }
    }
    
    // Parse MBTI text
    function parseMBTIText(text) {
        const questions = [];
        const lines = text.split('\n');
        let currentQuestion = null;
        
        for (let line of lines) {
            line = line.trim();
            if (!line) continue;
            
            // Match question (number + text)
            const questionMatch = line.match(/^(\d+)\.\s*(.+)$/);
            if (questionMatch) {
                if (currentQuestion) {
                    questions.push(currentQuestion);
                }
                
                currentQuestion = {
                    text: questionMatch[2].trim(),
                    answers: [],
                    required: false
                };
                continue;
            }
            
            // Match option with MBTI letter: A) text (E)
            const optionWithLetterMatch = line.match(/^([AB])\)\s*(.+?)\s*\(([EISNTFJP])\)$/);
            if (optionWithLetterMatch && currentQuestion) {
                currentQuestion.answers.push({
                    text: optionWithLetterMatch[2].trim(),
                    letter: optionWithLetterMatch[3],
                    score: 1
                });
                continue;
            }
            
            // Match option without MBTI letter: A) text
            const optionMatch = line.match(/^([AB])\)\s*(.+)$/);
            if (optionMatch && currentQuestion) {
                currentQuestion.answers.push({
                    text: optionMatch[2].trim(),
                    letter: '',
                    score: 1
                });
            }
        }
        
        // Add last question
        if (currentQuestion) {
            questions.push(currentQuestion);
        }
        
        return questions;
    }
    
    // Show import preview
    function showImportPreview(questions) {
        const previewDiv = document.getElementById('import_preview');
        const contentDiv = document.getElementById('preview_content');
        
        let html = `<div style="max-height: 400px; overflow-y: auto;">`;
        html += `<p><strong>تعداد سوالات یافت شده: ${questions.length}</strong></p>`;
        
        questions.forEach((question, index) => {
            html += `<div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">`;
            html += `<h5 style="margin: 0 0 10px 0; color: #0073aa;">سوال ${index + 1}:</h5>`;
            html += `<p style="margin: 0 0 10px 0;"><strong>متن:</strong> ${question.text}</p>`;
            
            if (question.answers.length > 0) {
                html += `<div style="margin-left: 20px;">`;
                question.answers.forEach((answer, answerIndex) => {
                    const letter = String.fromCharCode(65 + answerIndex); // A, B, C, ...
                    const mbtiLetter = answer.letter ? ` (${answer.letter})` : '';
                    html += `<p style="margin: 5px 0;"><strong>${letter}):</strong> ${answer.text}${mbtiLetter}</p>`;
                });
                html += `</div>`;
            }
            
            html += `</div>`;
        });
        
        html += `</div>`;
        contentDiv.innerHTML = html;
        previewDiv.style.display = 'block';
    }
    
    // Import questions
    function importQuestions(questions) {
        // Get existing questions
        const container = document.getElementById('questions-container');
        const existingQuestions = container.querySelectorAll('.question-group');
        const startIndex = existingQuestions.length;
        
        // Add new questions to existing ones
        questions.forEach((question, index) => {
            addQuestionToContainer(question, startIndex + index);
        });
        
        // Update question numbers
        updateQuestionNumbers();
        
        // Hide preview
        document.getElementById('import_preview').style.display = 'none';
        
        // Show success message
        alert(`✅ ${questions.length} سوال با موفقیت به سوالات موجود اضافه شد!`);
    }
    
    // Add question to container
    function addQuestionToContainer(question, index) {
        const container = document.getElementById('questions-container');
        
        let answersHtml = '';
        question.answers.forEach((answer, answerIndex) => {
            answersHtml += `
                <div class="answer-row">
                    <input type="text" name="questions[${index}][answers][${answerIndex}][text]" value="${answer.text}" placeholder="پاسخ" />
                    <input type="text" name="questions[${index}][answers][${answerIndex}][letter]" value="${answer.letter}" placeholder="حرف" />
                    <input type="number" name="questions[${index}][answers][${answerIndex}][score]" value="${answer.score}" placeholder="امتیاز" />
                    <button type="button" class="remove-answer"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="18" height="18"><path d="M3.726563 3.023438L3.023438 3.726563L7.292969 8L3.023438 12.269531L3.726563 12.980469L8 8.707031L12.269531 12.980469L12.980469 12.269531L8.707031 8L12.980469 3.726563L12.269531 3.023438L8 7.292969Z" fill="#FFFFFF" /></svg></button>
                </div>
            `;
        });
        
        const questionHtml = `
            <div class="question-group" data-q="${index}">
                <h4>سوال ${index + 1}</h4>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <input type="text" name="questions[${index}][text]" value="${question.text}" placeholder="متن سوال" style="flex:1;" />
                    <label style="display:flex;align-items:center;gap:5px;font-size:12px;color:#666;">
                        <input type="checkbox" name="questions[${index}][required]" value="1" ${question.required ? 'checked' : ''} class="question-required-checkbox">
                        ضروری
                    </label>
                </div>
                <div class="answers-container">
                    ${answersHtml}
                </div>
                <button type="button" class="add-answer">افزودن پاسخ</button>
                <button type="button" class="remove-question"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="18" height="18"><path d="M3.726563 3.023438L3.023438 3.726563L7.292969 8L3.023438 12.269531L3.726563 12.980469L8 8.707031L12.269531 12.980469L12.980469 12.269531L8.707031 8L12.980469 3.726563L12.269531 3.023438L8 7.292969Z" /></svg></button>
                <button type="button" class="duplicate-question">📄 کپی سوال</button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', questionHtml);
    }
    
    // Export questions
    function exportQuestions(format) {
        const questions = [];
        const questionGroups = document.querySelectorAll('.question-group');
        
        questionGroups.forEach((group, index) => {
            const questionText = group.querySelector('input[name*="[text]"]').value;
            const required = group.querySelector('input[name*="[required]"]').checked;
            const answers = [];
            
            group.querySelectorAll('.answer-row').forEach((answerRow, answerIndex) => {
                const text = answerRow.querySelector('input[name*="[text]"]').value;
                const letter = answerRow.querySelector('input[name*="[letter]"]').value;
                const score = parseInt(answerRow.querySelector('input[name*="[score]"]').value) || 1;
                
                if (text.trim()) {
                    answers.push({ text, letter, score });
                }
            });
            
            if (questionText.trim()) {
                questions.push({
                    text: questionText,
                    answers: answers,
                    required: required
                });
            }
        });
        
        if (questions.length === 0) {
            alert('هیچ سوالی برای برون‌ریزی یافت نشد.');
            return;
        }
        
        let content = '';
        let filename = 'psychology_test_questions';
        let mimeType = 'application/json';
        
        switch (format) {
            case 'json':
                content = JSON.stringify(questions, null, 2);
                filename += '.json';
                break;
                
            case 'csv':
                content = 'سوال,گزینه A,حرف A,امتیاز A,گزینه B,حرف B,امتیاز B\n';
                questions.forEach(question => {
                    const answerA = question.answers[0] || { text: '', letter: '', score: 1 };
                    const answerB = question.answers[1] || { text: '', letter: '', score: 1 };
                    
                    content += `"${question.text.replace(/"/g, '""')}","${answerA.text.replace(/"/g, '""')}","${answerA.letter}",${answerA.score},"${answerB.text.replace(/"/g, '""')}","${answerB.letter}",${answerB.score}\n`;
                });
                filename += '.csv';
                mimeType = 'text/csv';
                break;
                
            case 'mbti_text':
                questions.forEach((question, index) => {
                    content += `${index + 1}. ${question.text}\n`;
                    question.answers.forEach((answer, answerIndex) => {
                        const letter = String.fromCharCode(65 + answerIndex);
                        const mbtiLetter = answer.letter ? ` (${answer.letter})` : '';
                        content += `   ${letter}) ${answer.text}${mbtiLetter}\n`;
                    });
                    content += '\n';
                });
                filename += '.txt';
                mimeType = 'text/plain';
                break;
        }
        
        // Download file
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        alert(`✅ فایل ${filename} با موفقیت دانلود شد!`);
    }

    // Font Management System
    function initFontSystem() {
        const uploadButtons = document.querySelectorAll('.upload-font-btn');
        const fontSelects = document.querySelectorAll('.font-select');
        
        // Initialize font previews
        updateAllFontPreviews();
        
        // Add event listeners for font uploads
        uploadButtons.forEach(button => {
            button.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                const fontType = this.getAttribute('data-font-type');
                openFontUploader(target, fontType);
            });
        });
        
        // Add event listeners for font selection changes
        fontSelects.forEach(select => {
            select.addEventListener('change', function() {
                updateFontPreview(this);
            });
        });
        
        // Add event listeners for font weight changes
        const weightSelects = document.querySelectorAll('[id*="_weight"]');
        weightSelects.forEach(select => {
            select.addEventListener('change', function() {
                updateFontPreviewByWeight(this);
            });
        });
    }
    
    // Open WordPress media uploader for fonts
    function openFontUploader(target, fontType) {
        if (typeof wp !== 'undefined' && wp.media) {
            const frame = wp.media({
                title: 'انتخاب فونت',
                button: {
                    text: 'انتخاب فونت'
                },
                multiple: false,
                library: {
                    type: ['application/font-woff', 'application/font-woff2', 'application/x-font-ttf', 'application/x-font-otf']
                }
            });
            
            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                const fontFile = attachment.url;
                const fontName = attachment.title || attachment.filename.replace(/\.[^/.]+$/, '');
                
                // Update hidden inputs
                document.getElementById(`psychology_test_${target}_font_file`).value = fontFile;
                document.getElementById(`psychology_test_${target}_font_name`).value = fontName;
                
                // Update select to custom
                document.getElementById(`psychology_test_${target}_font`).value = 'custom';
                
                // Update preview
                updateFontPreview(document.getElementById(`psychology_test_${target}_font`));
                
                // Show custom font info
                showCustomFontInfo(target, fontName, fontFile);
            });
            
            frame.open();
        } else {
            alert('سیستم رسانه وردپرس در دسترس نیست. لطفاً صفحه را رفرش کنید.');
        }
    }
    
    // Update font preview
    function updateFontPreview(selectElement) {
        const target = selectElement.id.replace('psychology_test_', '').replace('_font', '');
        const selectedValue = selectElement.value;
        const previewElement = document.getElementById(`${target}-font-preview`);
        
        if (!previewElement) return;
        
        let fontFamily = 'inherit';
        let fontWeight = 'normal';
        
        // Get font weight
        const weightSelect = document.getElementById(`psychology_test_${target}_weight`);
        if (weightSelect) {
            fontWeight = weightSelect.value;
        }
        
        // Set font family based on selection
        if (selectedValue === 'custom') {
            const fontNameInput = document.getElementById(`psychology_test_${target}_font_name`);
            if (fontNameInput && fontNameInput.value) {
                fontFamily = `'${fontNameInput.value}'`;
            }
        } else if (selectedValue && selectedValue !== 'inherit') {
            fontFamily = selectedValue;
        }
        
        // Apply font to preview
        const previewText = previewElement.querySelector('.preview-text');
        const previewButton = previewElement.querySelector('.preview-button');
        
        if (previewText) {
            previewText.style.fontFamily = fontFamily;
            previewText.style.fontWeight = fontWeight;
        }
        
        if (previewButton) {
            previewButton.style.fontFamily = fontFamily;
            previewButton.style.fontWeight = fontWeight;
        }
        
        // Load custom font if needed
        if (selectedValue === 'custom') {
            loadCustomFont(target);
        }
    }
    
    // Update font preview by weight change
    function updateFontPreviewByWeight(weightSelect) {
        const target = weightSelect.id.replace('psychology_test_', '').replace('_weight', '');
        const fontSelect = document.getElementById(`psychology_test_${target}_font`);
        
        if (fontSelect) {
            updateFontPreview(fontSelect);
        }
    }
    
    // Load custom font
    function loadCustomFont(target) {
        const fontFileInput = document.getElementById(`psychology_test_${target}_font_file`);
        const fontNameInput = document.getElementById(`psychology_test_${target}_font_name`);
        
        if (!fontFileInput || !fontNameInput || !fontFileInput.value || !fontNameInput.value) {
            return;
        }
        
        const fontFile = fontFileInput.value;
        const fontName = fontNameInput.value;
        const ext = fontFile.split('.').pop().toLowerCase();
        
        // Remove existing font face
        const existingStyle = document.querySelector(`style[data-custom-font="${target}"]`);
        if (existingStyle) {
            existingStyle.remove();
        }
        
        // Create new font face
        const style = document.createElement('style');
        style.setAttribute('data-custom-font', target);
        
        let format = 'truetype';
        if (ext === 'otf') format = 'opentype';
        else if (ext === 'woff') format = 'woff';
        else if (ext === 'woff2') format = 'woff2';
        
        style.textContent = `
            @font-face {
                font-family: '${fontName}';
                src: url('${fontFile}') format('${format}');
                font-weight: normal;
                font-style: normal;
                font-display: swap;
            }
        `;
        
        document.head.appendChild(style);
    }
    
    // Show custom font info
    function showCustomFontInfo(target, fontName, fontFile) {
        const fontSelect = document.getElementById(`psychology_test_${target}_font`);
        const container = fontSelect.closest('.setting-input');
        
        // Remove existing info
        const existingInfo = container.querySelector('.custom-font-info');
        if (existingInfo) {
            existingInfo.remove();
        }
        
        // Add new info
        const infoHtml = `
            <div class="custom-font-info">
                <strong>فونت سفارشی:</strong> ${fontName}<br>
                <strong>فایل:</strong> ${fontFile.split('/').pop()}
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', infoHtml);
    }
    
    // Update all font previews
    function updateAllFontPreviews() {
        const fontSelects = document.querySelectorAll('.font-select');
        fontSelects.forEach(select => {
            updateFontPreview(select);
        });
    }
    
    // Initialize font system when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFontSystem);
    } else {
        initFontSystem();
    }
});