// Chatbot Functionality
        const chatbotToggle = document.getElementById('chatbotToggle');
        const chatbotWindow = document.getElementById('chatbotWindow');
        const chatbotClose = document.getElementById('chatbotClose');
        const chatbotMessages = document.getElementById('chatbotMessages');
        const chatbotInput = document.getElementById('chatbotInput');
        const chatbotSend = document.getElementById('chatbotSend');
        const suggestionsContainer = document.getElementById('chatbotSuggestions');
        
        // Website content knowledge base
        const websiteKnowledge = {
            services: {
                title: "Our Services",
                content: extractServices() || "We offer web development, design, and digital marketing services. Visit our Services page for details."
            },
            pricing: {
                title: "Pricing Information",
                content: extractPricing() || "Our pricing starts at $500 for basic websites. Custom solutions are priced based on requirements."
            },
            contact: {
                title: "Contact Us",
                content: extractContact() || "Email us at contact@example.com or call (123) 456-7890. We're available 9am-5pm weekdays."
            },
            about: {
                title: "About Us",
                content: extractAbout() || "We're a digital agency focused on creating innovative solutions for our clients."
            }
        };
        
        // Initialize with welcome message
        setTimeout(() => {
            addBotMessage("Hi! I can answer questions about this website. Try asking about our services, pricing, or contact information.");
            showSuggestions();
        }, 1000);
        
        // Toggle chatbot visibility
        chatbotToggle.addEventListener('click', () => {
            chatbotWindow.classList.toggle('active');
            if (chatbotWindow.classList.contains('active')) {
                chatbotInput.focus();
            }
        });
        
        chatbotClose.addEventListener('click', () => {
            chatbotWindow.classList.remove('active');
        });
        
        // Add message to chat
        function addMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', `${type}-message`);
            messageDiv.textContent = text;
            chatbotMessages.appendChild(messageDiv);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }
        
        // Show quick suggestions
        function showSuggestions() {
            suggestionsContainer.innerHTML = '';
            const suggestions = [
                "What services do you offer?",
                "How much does it cost?",
                "How can I contact you?",
                "Tell me about your company"
            ];
            
            suggestions.forEach(text => {
                const button = document.createElement('button');
                button.classList.add('suggestion-btn');
                button.textContent = text;
                button.addEventListener('click', () => {
                    chatbotInput.value = text;
                    chatbotSend.click();
                });
                suggestionsContainer.appendChild(button);
            });
        }
        
        // Process user questions
        function processQuestion(question) {
            const lowerQ = question.toLowerCase();
            
            // Show typing indicator
            const typingIndicator = document.createElement('div');
            typingIndicator.classList.add('message', 'bot-message');
            typingIndicator.textContent = "...";
            typingIndicator.id = "typingIndicator";
            chatbotMessages.appendChild(typingIndicator);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            
            // Simulate typing delay
            setTimeout(() => {
                document.getElementById('typingIndicator').remove();
                
                if (lowerQ.includes('service') || lowerQ.includes('offer')) {
                    showInfoCard('services');
                } 
                else if (lowerQ.includes('price') || lowerQ.includes('cost')) {
                    showInfoCard('pricing');
                }
                else if (lowerQ.includes('contact') || lowerQ.includes('reach')) {
                    showInfoCard('contact');
                }
                else if (lowerQ.includes('about') || lowerQ.includes('company')) {
                    showInfoCard('about');
                }
                else {
                    addMessage("I can answer questions about our services, pricing, and contact information. Try asking something like 'What services do you offer?'", 'bot');
                    showSuggestions();
                }
            }, 800);
        }
        
        // Display information card
        function showInfoCard(topic) {
            const info = websiteKnowledge[topic];
            const card = document.createElement('div');
            card.classList.add('message', 'bot-message');
            
            card.innerHTML = `
                <div class="info-card">
                    <h4>${info.title}</h4>
                    <p>${info.content}</p>
                </div>
                <p>Would you like to know anything else?</p>
            `;
            
            chatbotMessages.appendChild(card);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            showSuggestions();
        }
        
        // Functions to extract content from your website
        function extractServices() {
            // Try to get services from the page
            const servicesSection = document.querySelector('#services, .services-section');
            if (servicesSection) {
                return servicesSection.textContent.trim().substring(0, 200) + "...";
            }
            return null;
        }
        
        function extractPricing() {
            // Try to get pricing from the page
            const pricingSection = document.querySelector('#pricing, .pricing-section');
            if (pricingSection) {
                return pricingSection.textContent.trim().substring(0, 200) + "...";
            }
            return null;
        }
        
        function extractContact() {
            // Try to get contact info from the page
            const contactSection = document.querySelector('#contact, .contact-section');
            if (contactSection) {
                return contactSection.textContent.trim().substring(0, 200) + "...";
            }
            return null;
        }
        
        function extractAbout() {
            // Try to get about info from the page
            const aboutSection = document.querySelector('#about, .about-section');
            if (aboutSection) {
                return aboutSection.textContent.trim().substring(0, 200) + "...";
            }
            return null;
        }
        
        // Event listeners
        chatbotSend.addEventListener('click', () => {
            const message = chatbotInput.value.trim();
            if (message) {
                addMessage(message, 'user');
                chatbotInput.value = '';
                processQuestion(message);
            }
        });
        
        chatbotInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                chatbotSend.click();
            }
        });
