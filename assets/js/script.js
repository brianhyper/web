// Main JS file ya website
    
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;

        function toggleTheme() {
            const isDark = body.getAttribute('data-theme') !== 'light';
            body.setAttribute('data-theme', isDark ? 'light' : 'dark');
            themeToggle.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
            
            // Update particles.js for light mode
            if (isDark) {
                particlesJS("particles-js", lightParticlesConfig);
            } else {
                particlesJS("particles-js", darkParticlesConfig);
            }
        }

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        body.setAttribute('data-theme', savedTheme);
        themeToggle.innerHTML = savedTheme === 'light' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        themeToggle.addEventListener('click', toggleTheme);

        // Particles.js Configurations
        const darkParticlesConfig = {
            "particles": {
                "number": {
                    "value": 100,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": ["#00ccff", "#6a00ff", "#ff00aa"]
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    },
                    "polygon": {
                        "nb_sides": 5
                    }
                },
                "opacity": {
                    "value": 0.5,
                    "random": true,
                    "anim": {
                        "enable": true,
                        "speed": 1,
                        "opacity_min": 0.1,
                        "sync": false
                    }
                },
                "size": {
                    "value": 3,
                    "random": true,
                    "anim": {
                        "enable": true,
                        "speed": 2,
                        "size_min": 0.1,
                        "sync": false
                    }
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#00ccff",
                    "opacity": 0.2,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 3,
                    "direction": "none",
                    "random": true,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false,
                    "attract": {
                        "enable": true,
                        "rotateX": 600,
                        "rotateY": 1200
                    }
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "grab"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 200,
                        "line_linked": {
                            "opacity": 0.5
                        }
                    },
                    "bubble": {
                        "distance": 400,
                        "size": 40,
                        "duration": 2,
                        "opacity": 8,
                        "speed": 3
                    },
                    "repulse": {
                        "distance": 200,
                        "duration": 0.4
                    },
                    "push": {
                        "particles_nb": 6
                    },
                    "remove": {
                        "particles_nb": 2
                    }
                }
            },
            "retina_detect": true
        };

        const lightParticlesConfig = {
            ...darkParticlesConfig,
            particles: {
                ...darkParticlesConfig.particles,
                color: {
                    value: ["#0066cc", "#4b0082", "#cc0066"]
                },
                line_linked: {
                    ...darkParticlesConfig.particles.line_linked,
                    color: "#0066cc"
                }
            }
        };

        // Initialize particles.js
        particlesJS("particles-js", savedTheme === 'light' ? lightParticlesConfig : darkParticlesConfig);

        // Three.js 3D Logo Animation
        const container = document.getElementById('canvas3d');
        
        if (THREE) {
            const scene = new THREE.Scene();
            scene.background = null;
            const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
            const renderer = new THREE.WebGLRenderer({ 
                alpha: true, 
                antialias: true,
                powerPreference: "high-performance"
            });
            
            renderer.setPixelRatio(window.devicePixelRatio);
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.shadowMap.enabled = true;
            container.appendChild(renderer.domElement);
            
            // Create a glowing torus knot
            const geometry = new THREE.TorusKnotGeometry(1, 0.4, 100, 16);
            const material = new THREE.MeshPhongMaterial({
                color: 0x00ccff,
                emissive: 0x0066ff,
                emissiveIntensity: 0.8,
                specular: 0xffffff,
                shininess: 100,
                transparent: true,
                opacity: 0.9
            });
            
            const knot = new THREE.Mesh(geometry, material);
            knot.castShadow = true;
            scene.add(knot);
            
            // Add lights
            const ambientLight = new THREE.AmbientLight(0x404040, 0.5);
            scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
            directionalLight.position.set(1, 1, 1);
            directionalLight.castShadow = true;
            scene.add(directionalLight);
            
            const pointLight = new THREE.PointLight(0x00ccff, 2, 50);
            pointLight.position.set(5, 5, 5);
            pointLight.castShadow = true;
            scene.add(pointLight);
            
            // Add glow effect
            const glowGeometry = new THREE.TorusKnotGeometry(1.1, 0.45, 100, 16);
            const glowMaterial = new THREE.MeshBasicMaterial({
                color: 0x00ccff,
                transparent: true,
                opacity: 0.2,
                blending: THREE.AdditiveBlending
            });
            const glow = new THREE.Mesh(glowGeometry, glowMaterial);
            scene.add(glow);
            
            camera.position.z = 5;
            
            // Animation loop
            function animate() {
                requestAnimationFrame(animate);
                
                knot.rotation.x += 0.005;
                knot.rotation.y += 0.01;
                glow.rotation.x += 0.005;
                glow.rotation.y += 0.01;
                
                // Pulsing glow effect
                const pulse = Math.sin(Date.now() * 0.002) * 0.1 + 0.9;
                glow.scale.set(pulse, pulse, pulse);
                
                renderer.render(scene, camera);
            }
            
            animate();
            
            // Handle window resize
            window.addEventListener('resize', () => {
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            });
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Case study tabs
        const caseTabs = document.querySelectorAll('.case-tab');
        const caseCards = document.querySelectorAll('.case-card');
        
        caseTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Update active tab
                caseTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                const category = tab.dataset.category;
                
                // Filter case studies
                caseCards.forEach(card => {
                    if (category === 'all' || card.dataset.category === category) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Testimonial slider
        const testimonialTrack = document.querySelector('.testimonial-track');
        const testimonialCards = document.querySelectorAll('.testimonial-card');
        const sliderDots = document.querySelectorAll('.slider-dot');
        let currentSlide = 0;
        
        function updateSlider() {
            const cardWidth = testimonialCards[0].offsetWidth + 32; // including gap
            testimonialTrack.style.transform = `translateX(-${currentSlide * cardWidth}px)`;
            
            // Update dots
            sliderDots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }
        
        sliderDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateSlider();
            });
        });
        
        // Auto-advance slider
        setInterval(() => {
            currentSlide = (currentSlide + 1) % testimonialCards.length;
            updateSlider();
        }, 5000);

        // FAQ accordion
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            question.addEventListener('click', () => {
                // Close other items
                faqItems.forEach(i => {
                    if (i !== item) {
                        i.classList.remove('active');
                    }
                });
                
                // Toggle current item
                item.classList.toggle('active');
            });
        });

        // Animated counter for stats
        const counters = document.querySelectorAll('.stat-number');
        const speed = 200;
        
        function animateCounters() {
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-count');
                const count = +counter.innerText;
                const increment = target / speed;
                
                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(animateCounters, 1);
                } else {
                    counter.innerText = target;
                }
            });
        }
        
        // Intersection Observer for scroll animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    
                    // Start counter animation when stats section is visible
                    if (entry.target.classList.contains('stats-section')) {
                        animateCounters();
                    }
                }
            });
        }, {
            threshold: 0.1
        });
        
        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Form submission
        const contactForm = document.getElementById('contactForm');
        
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;
            
            // Here you would typically send the data to a server
            console.log('Form submitted:', { name, email, subject, message });
            
            // Show success message
            alert('Thank you for your message! We will get back to you soon.');
            contactForm.reset();
        });

        // Add glow effect to form inputs on focus
        const formInputs = document.querySelectorAll('.form-control');
        formInputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.style.boxShadow = '0 0 15px rgba(0, 204, 255, 0.3)';
                input.style.borderColor = 'rgba(0, 204, 255, 0.5)';
            });
            
            input.addEventListener('blur', () => {
                input.style.boxShadow = 'none';
                input.style.borderColor = 'rgba(255, 255, 255, 0.1)';
            });
        });

        // Hover effect for service cards
        const serviceCards = document.querySelectorAll('.service-card');
        
        serviceCards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                card.style.setProperty('--mouse-x', `${x}px`);
                card.style.setProperty('--mouse-y', `${y}px`);
            });
        });

        