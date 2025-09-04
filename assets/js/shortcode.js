document.addEventListener("DOMContentLoaded", function () {
    // Initialize Big Five theme functionality
    initializeBigFiveTheme();
    
    // Check if we're on a result page
    const urlParams = new URLSearchParams(window.location.search);
    const resultId = urlParams.get('result');
    
    if (resultId) {
        // We're on a result page, hide the test form
        const testForm = document.querySelector('.psychology-test-form');
        if (testForm) {
            testForm.style.display = 'none';
        }
        
        // Show the result container
        const resultContainer = document.getElementById('test-result');
        if (resultContainer) {
            resultContainer.style.display = 'block';
        }
        
        // Add retake functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('retake-test')) {
                e.preventDefault();
                // Remove result parameter and reload
                const cleanUrl = window.location.pathname;
                window.location.href = cleanUrl;
            }
        });
        
        return; // Don't initialize test functionality on result pages
    }
    
    // Check if we're coming from retake and clean URL
    if (window.location.search.includes('retake=1')) {
        // Remove retake parameter from URL
        const cleanUrl = window.location.pathname;
        window.history.replaceState(null, '', cleanUrl);
    }
    
    // Clear any existing form data on page load
    const forms = document.querySelectorAll(".psychology-test-form");
    forms.forEach(form => {
        // Clear any hidden inputs that might contain test data
        const hiddenInputs = form.querySelectorAll('input[name="test_answers"], input[name="time_expired"]');
        hiddenInputs.forEach(input => input.remove());
    });
    
    // Hide any existing result display
    const resultDivs = document.querySelectorAll("#test-result");
    resultDivs.forEach(resultDiv => {
        resultDiv.style.display = "none";
    });
    
    document.querySelectorAll(".psychology-test-form").forEach(function (form) {
        const container = form.closest('.psychology-test-container');
        const questions = Array.from(form.querySelectorAll(".question-card"));
        if (!questions.length) return;

        // Get elements
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');
        const currentPageEl = container.querySelector(".current-page");
        const progressFill = document.getElementById('test-progress-fill');
        const answeredCountEl = document.getElementById('answered-count');
        const timerDisplay = document.getElementById('timer-display');
        const timerProgressBar = document.getElementById('timer-progress-bar');

        // Get form data
        const timeLimit = parseInt(form.dataset.timeLimit) || 0;
        const totalQuestions = parseInt(form.dataset.totalQuestions) || questions.length;
        const perPage = parseInt(form.dataset.perPage) || 1;
        const requiredMode = form.dataset.requiredMode || 'optional';
        const requiredQuestions = JSON.parse(form.dataset.requiredQuestions || '[]');
        
        let timerInterval = null;
        let timeRemaining = timeLimit;
        let initialTimeLimit = timeLimit;
        let currentPage = 1;
        let totalPages = Math.ceil(totalQuestions / perPage);

        // Initialize
        updateProgress();
        updateAnsweredQuestions();
        updateNavigation();
        if (timeLimit > 0) {
            startTimer();
        }

        // Timer functions
        function startTimer() {
            if (timeLimit <= 0) return;
            
            timerInterval = setInterval(function() {
                timeRemaining--;
                updateTimerDisplay();
                updateTimerProgress();
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    submitTest(true);
                    return;
                }
            }, 1000);
        }
        
        function stopTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }

        function updateTimerDisplay() {
            if (!timerDisplay) return;
            
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Add warning classes
            timerDisplay.classList.remove('warning', 'danger');
            if (timeRemaining <= 30) {
                timerDisplay.classList.add('danger');
            } else if (timeRemaining <= 60) {
                timerDisplay.classList.add('warning');
            }
        }

        function updateTimerProgress() {
            if (!timerProgressBar || initialTimeLimit <= 0) return;
            
            const percentage = (timeRemaining / initialTimeLimit) * 100;
            timerProgressBar.style.width = `${percentage}%`;
        }

        function updateProgress() {
            const answered = form.querySelectorAll('input[type="radio"]:checked').length;
            const percentage = (answered / totalQuestions) * 100;
            
            if (progressFill) {
                progressFill.style.width = `${percentage}%`;
            }
            
            if (answeredCountEl) {
                answeredCountEl.textContent = answered;
            }
        }

        function updateNavigation() {
            if (currentPageEl) {
                currentPageEl.textContent = currentPage;
            }
            
            if (prevBtn) {
                prevBtn.disabled = currentPage <= 1;
            }
            
            if (nextBtn) {
                nextBtn.disabled = currentPage >= totalPages;
                nextBtn.style.display = currentPage >= totalPages ? 'none' : 'flex';
            }
            
            if (submitBtn) {
                submitBtn.style.display = currentPage >= totalPages ? 'flex' : 'none';
            }
        }

        function showPage(page) {
            // Hide all questions
            questions.forEach(function (question) {
                question.style.display = 'none';
            });
            
                    // Show questions for current page
        const startIndex = (page - 1) * perPage;
        const endIndex = Math.min(startIndex + perPage, questions.length);
        
        for (let i = startIndex; i < endIndex; i++) {
            if (questions[i]) {
                questions[i].style.display = 'block';
                // Add animation
                questions[i].style.animation = 'slideIn 0.5s ease-out';
            }
        }
        
        // Hide other questions
        questions.forEach((question, index) => {
            if (index < startIndex || index >= endIndex) {
                question.style.display = 'none';
            }
        });
            
            currentPage = page;
            updateNavigation();
            
            // Scroll to top of form
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        // Make showPage accessible globally for Big Five theme
        window.psychologyTestMainScope = {
            showPage: showPage
        };

        function submitTest(timeExpired = false) {
            stopTimer();
            
            // Hide timer with animation
            const timerContainer = container.querySelector('.test-timer');
            if (timerContainer) {
                timerContainer.style.animation = 'fadeOut 0.5s ease-out';
                setTimeout(() => {
                    timerContainer.style.display = 'none';
                }, 500);
            }
            
            // جمع‌آوری پاسخ‌ها
            const answers = [];
            form.querySelectorAll('input[type="radio"]:checked').forEach(function (input) {
                const questionIndex = input.name.replace('question_', '');
                const letter = (input.dataset.letter || "").toUpperCase().trim();
                const score = parseInt(input.value, 10) || 0;
                
                answers.push({
                    question_index: questionIndex,
                    letter: letter,
                    score: score
                });
            });

            // ایجاد input hidden برای ارسال پاسخ‌ها
            let answersInput = form.querySelector('input[name="test_answers"]');
            if (!answersInput) {
                answersInput = document.createElement('input');
                answersInput.type = 'hidden';
                answersInput.name = 'test_answers';
                form.appendChild(answersInput);
            }
            answersInput.value = JSON.stringify(answers);

            // اضافه کردن input برای timeExpired
            let timeExpiredInput = form.querySelector('input[name="time_expired"]');
            if (!timeExpiredInput) {
                timeExpiredInput = document.createElement('input');
                timeExpiredInput.type = 'hidden';
                timeExpiredInput.name = 'time_expired';
                form.appendChild(timeExpiredInput);
            }
            timeExpiredInput.value = timeExpired ? '1' : '0';

            // تغییر action فرم
            form.action = window.location.href;
            form.method = 'POST';

            // ارسال فرم
            form.submit();
        }



        // Event listeners
        if (prevBtn) {
            prevBtn.addEventListener("click", function (e) {
                e.preventDefault();
                if (currentPage > 1) {
                    showPage(currentPage - 1);
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener("click", function (e) {
                e.preventDefault();
                if (currentPage < totalPages) {
                    showPage(currentPage + 1);
                }
            });
        }

        if (submitBtn) {
            submitBtn.addEventListener("click", function (e) {
                e.preventDefault();
                
                // Check required questions
                if (!checkRequiredQuestions()) {
                    alert('لطفاً به تمام سوالات ضروری پاسخ دهید.');
                    return;
                }
                
                // Confirm submission
                if (confirm('آیا مطمئن هستید که می‌خواهید آزمون را تمام کنید؟')) {
                    submitTest(false);
                }
            });
        }

        // Update progress when answers change
        form.addEventListener('change', function(e) {
            if (e.target.type === 'radio') {
                updateProgress();
                updateAnsweredQuestions();
                
                // Add selection animation
                const answerOption = e.target.closest('.answer-option');
                if (answerOption) {
                    answerOption.style.animation = 'none';
                    setTimeout(() => {
                        answerOption.style.animation = 'pulse 0.5s ease-out';
                    }, 10);
                }
                
                // Auto-scroll to next question after a short delay
                setTimeout(() => {
                    const currentQuestion = e.target.closest('.question-card');
                    if (currentQuestion) {
                        const currentQuestionIndex = questions.indexOf(currentQuestion);
                        const nextQuestion = questions[currentQuestionIndex + 1];
                        
                        if (nextQuestion) {
                            // If next question is on the same page, just scroll to it
                            if (nextQuestion.style.display !== 'none') {
                                nextQuestion.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            } else {
                                // If next question is on next page, go to next page
                                const nextPage = Math.floor((currentQuestionIndex + 1) / perPage) + 1;
                                if (nextPage <= totalPages) {
                                    showPage(nextPage);
                                }
                            }
                        }
                    }
                }, 800); // 800ms delay for user to see the selection
            }
        });
        
        // Function to update answered questions styling
        function updateAnsweredQuestions() {
            questions.forEach(function(question, index) {
                const radioInputs = question.querySelectorAll('input[type="radio"]');
                const isAnswered = Array.from(radioInputs).some(input => input.checked);
                
                if (isAnswered) {
                    question.classList.add('answered');
                } else {
                    question.classList.remove('answered');
                }
                
                // Add required styling
                const isRequired = isQuestionRequired(index);
                if (isRequired) {
                    question.classList.add('required');
                } else {
                    question.classList.remove('required');
                }
            });
        }
        
        // Function to check if a question is required
        function isQuestionRequired(questionIndex) {
            if (requiredMode === 'required') {
                return true;
            } else if (requiredMode === 'custom') {
                return requiredQuestions[questionIndex] === true;
            }
            return false;
        }
        
        // Function to check if all required questions are answered
        function checkRequiredQuestions() {
            if (requiredMode === 'optional') {
                return true;
            }
            
            let allRequiredAnswered = true;
            questions.forEach(function(question, index) {
                if (isQuestionRequired(index)) {
                    const radioInputs = question.querySelectorAll('input[type="radio"]');
                    const isAnswered = Array.from(radioInputs).some(input => input.checked);
                    if (!isAnswered) {
                        allRequiredAnswered = false;
                    }
                }
            });
            
            return allRequiredAnswered;
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.target.closest('.psychology-test-container') !== container) return;
            
            switch(e.key) {
                case 'ArrowLeft':
                    if (currentPage > 1) {
                        e.preventDefault();
                        showPage(currentPage - 1);
                    }
                    break;
                case 'ArrowRight':
                    if (currentPage < totalPages) {
                        e.preventDefault();
                        showPage(currentPage + 1);
                    }
                    break;
                case 'Enter':
                    if (e.ctrlKey && currentPage === totalPages) {
                        e.preventDefault();
                        submitTest(false);
                    }
                    break;
            }
        });

        // Initialize first page
        showPage(1);
    });
    
    // Event listener for retake test button
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('retake-test')) {
            e.preventDefault();
            
            // Reset form and show it
            const form = document.querySelector("#psychology-test-form");
            if (form) {
                form.reset();
                form.style.display = "block";
                
                // Clear all radio button selections
                const radioButtons = form.querySelectorAll('input[type="radio"]');
                radioButtons.forEach(radio => {
                    radio.checked = false;
                });
            }
            
            // Hide result
            const resultDiv = document.querySelector("#test-result");
            if (resultDiv) {
                resultDiv.style.display = "none";
            }
            
            // Clear URL parameters
            const url = new URL(window.location);
            url.searchParams.delete("test_answers");
            url.searchParams.delete("time_expired");
            window.history.replaceState({}, "", url);
            
            // Reset to first page
            const questions = document.querySelectorAll(".question-card");
            if (questions.length > 0) {
                questions.forEach((q, index) => {
                    if (index === 0) {
                        q.style.display = "block";
                    } else {
                        q.style.display = "none";
                    }
                });
            }
            
            // Reset progress
            const progressFill = document.getElementById('test-progress-fill');
            if (progressFill) {
                progressFill.style.width = "0%";
            }
            
            const answeredCountEl = document.getElementById('answered-count');
            if (answeredCountEl) {
                answeredCountEl.textContent = "0";
            }
            
            // Reset current page
            const currentPageEl = document.querySelector(".current-page");
            if (currentPageEl) {
                currentPageEl.textContent = "1";
            }
            
            // Reset navigation buttons
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const submitBtn = document.getElementById('submit-btn');
            
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) {
                nextBtn.disabled = false;
                nextBtn.style.display = 'flex';
            }
            if (submitBtn) submitBtn.style.display = 'none';
            
            // Reset answered questions styling
            questions.forEach(question => {
                question.classList.remove('answered');
            });
            
            // Clear URL parameters and reload the page
            setTimeout(() => {
                // Remove show_result parameter and reload
                const cleanUrl = window.location.pathname;
                window.location.replace(cleanUrl);
            }, 100);
        }
    });
});

// Add CSS animations dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-20px); }
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .question-card.answered {
        transition: opacity 0.3s ease, background-color 0.3s ease;
    }
`;
document.head.appendChild(style);

// تابع راه‌اندازی قالب Big Five
function initializeBigFiveTheme() {
    const container = document.querySelector('.psychology-test-container');
    if (!container || !container.classList.contains('big-five-theme')) {
        return;
    }

    // اضافه کردن event listener برای دایره‌های Big Five
    document.addEventListener('click', function(e) {
        if (e.target.closest('.big-five-circle')) {
            const circle = e.target.closest('.big-five-circle');
            const radioInput = circle.querySelector('input[type="radio"]');
            const questionCard = circle.closest('.question-card');
            
            if (radioInput && questionCard) {
                // حذف انتخاب قبلی از همه دایره‌های این سوال
                const allCirclesInQuestion = questionCard.querySelectorAll('.big-five-circle');
                allCirclesInQuestion.forEach(c => c.classList.remove('selected'));
                
                // انتخاب دایره فعلی
                circle.classList.add('selected');
                radioInput.checked = true;
                
                // اضافه کردن کلاس answered به سوال
                questionCard.classList.add('answered');
                
                // به‌روزرسانی پیشرفت
                updateProgress();
                updateAnsweredQuestions();
                updateNavigation();
                
                // اسکرول خودکار به سوال بعدی
                setTimeout(() => {
                    const questions = document.querySelectorAll('.question-card');
                    const currentQuestionIndex = Array.from(questions).indexOf(questionCard);
                    const nextQuestion = questions[currentQuestionIndex + 1];
                    
                    if (nextQuestion) {
                        // Get form data for pagination
                        const form = document.querySelector('.psychology-test-form');
                        const perPage = parseInt(form.dataset.perPage) || 1;
                        const totalQuestions = questions.length;
                        const totalPages = Math.ceil(totalQuestions / perPage);
                        
                        // If next question is on the same page, just scroll to it
                        if (nextQuestion.style.display !== 'none') {
                            nextQuestion.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        } else {
                            // If next question is on next page, go to next page
                            const nextPage = Math.floor((currentQuestionIndex + 1) / perPage) + 1;
                            if (nextPage <= totalPages) {
                                // Find and call the showPage function from the main scope
                                const mainScope = window.psychologyTestMainScope;
                                if (mainScope && mainScope.showPage) {
                                    mainScope.showPage(nextPage);
                                }
                            }
                        }
                    }
                }, 800);
            }
        }
    });

    // تابع به‌روزرسانی پیشرفت
    function updateProgress() {
        const questions = document.querySelectorAll('.question-card');
        const answeredQuestions = document.querySelectorAll('.question-card.answered');
        const progressFill = document.getElementById('test-progress-fill');
        
        if (progressFill && questions.length > 0) {
            const progress = (answeredQuestions.length / questions.length) * 100;
            progressFill.style.width = progress + '%';
        }
    }

    // تابع به‌روزرسانی تعداد سوالات پاسخ داده شده
    function updateAnsweredQuestions() {
        const answeredQuestions = document.querySelectorAll('.question-card.answered');
        const answeredCountEl = document.getElementById('answered-count');
        
        if (answeredCountEl) {
            answeredCountEl.textContent = answeredQuestions.length;
        }
    }

    // تابع به‌روزرسانی دکمه‌های ناوبری
    function updateNavigation() {
        const questions = document.querySelectorAll('.question-card');
        const answeredQuestions = document.querySelectorAll('.question-card.answered');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');
        const currentPageEl = document.querySelector('.current-page');
        
        // Get current page and total pages
        const form = document.querySelector('.psychology-test-form');
        const perPage = parseInt(form.dataset.perPage) || 1;
        const totalQuestions = questions.length;
        const totalPages = Math.ceil(totalQuestions / perPage);
        const currentPage = currentPageEl ? parseInt(currentPageEl.textContent) : 1;
        
        if (nextBtn && submitBtn) {
            // Show submit button only on last page
            if (currentPage >= totalPages) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'block';
            } else {
                nextBtn.style.display = 'flex';
                submitBtn.style.display = 'none';
            }
        }
    }
}