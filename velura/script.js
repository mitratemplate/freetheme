// Owl Carousel Initialization
$(document).ready(function() {
    // Hero Carousel
    $(".hero-carousel").owlCarousel({
        loop: true,
        margin: 10,
        nav: true,
        navText: ["<i class='fas fa-chevron-right'></i>", "<i class='fas fa-chevron-left'></i>"],
        dots: true,
        autoplay: true,
        autoplayTimeout: 5000,
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 1
            },
            1000: {
                items: 1
            }
        }
    });

    // Special Carousel
    $(".special-carousel").owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        navText: ["<i class='fas fa-chevron-right'></i>", "<i class='fas fa-chevron-left'></i>"],
        dots: true,
        autoplay: true,
        autoplayTimeout: 6000,
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 1
            },
            1000: {
                items: 1
            }
        }
    });
});

// Mobile Menu Toggle
document.getElementById('mobileMenuToggle').addEventListener('click', function() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
});

// Close menu when link is clicked
document.querySelectorAll('#mobileMenu a').forEach(link => {
    link.addEventListener('click', function() {
        document.getElementById('mobileMenu').classList.add('hidden');
    });
});

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function switchModal(closeId, openId) {
    closeModal(closeId);
    openModal(openId);
}

// Close modal when clicking on modal background
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal:not(.hidden)').forEach(modal => {
            closeModal(modal.id);
        });
    }
});

// Cart Management
let cart = [];
let favorites = [];
let compareList = [];

function addToCart(productName, price) {
    const product = {
        id: Date.now(),
        name: productName,
        price: price,
        quantity: 1
    };
    
    const existingProduct = cart.find(p => p.name === productName);
    if (existingProduct) {
        existingProduct.quantity++;
    } else {
        cart.push(product);
    }
    
    updateCart();
    showNotification('محصول به سبد خرید اضافه شد');
}

function removeFromCart(productId) {
    cart = cart.filter(p => p.id !== productId);
    updateCart();
}

function updateCart() {
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    const cartDiscount = document.getElementById('cartDiscount');
    const cartFinal = document.getElementById('cartFinal');
    const cartBadge = document.querySelector('.fa-shopping-cart').parentElement.querySelector('span');
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="text-gray-500 text-center py-8">سبد خرید شما خالی است</p>';
        cartTotal.textContent = '۰';
        cartDiscount.textContent = '۰';
        cartFinal.textContent = '۰';
        cartBadge.textContent = '0';
        return;
    }
    
    let html = '';
    let total = 0;
    let discount = 0;
    
    cart.forEach(product => {
        const itemTotal = product.price * product.quantity;
        const itemDiscount = Math.floor(itemTotal * 0.1); // 10% discount
        total += itemTotal;
        discount += itemDiscount;
        
        html += `
            <div class="flex gap-4 p-4 bg-gray-50 rounded-lg">
                <img src="https://images.unsplash.com/photo-1596777684687-81d440dc47d3?w=100" alt="${product.name}" class="w-20 h-20 object-cover rounded">
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800">${product.name}</h3>
                    <p class="text-primary font-bold">${formatPrice(product.price)}</p>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <div class="flex items-center gap-2 border border-gray-300 rounded">
                        <button onclick="changeQuantity(${product.id}, -1)" class="px-2 py-1">−</button>
                        <span class="px-3">${product.quantity}</span>
                        <button onclick="changeQuantity(${product.id}, 1)" class="px-2 py-1">+</button>
                    </div>
                    <button onclick="removeFromCart(${product.id})" class="text-red-500 hover:text-red-700 text-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    cartItems.innerHTML = html;
    cartTotal.textContent = formatPrice(total);
    cartDiscount.textContent = formatPrice(discount);
    cartFinal.textContent = formatPrice(total - discount);
    cartBadge.textContent = cart.length;
}

function changeQuantity(productId, change) {
    const product = cart.find(p => p.id === productId);
    if (product) {
        product.quantity += change;
        if (product.quantity <= 0) {
            removeFromCart(productId);
        } else {
            updateCart();
        }
    }
}

// Favorites Management
function toggleFavorite(button) {
    button.classList.toggle('text-red-500');
    button.classList.toggle('text-gray-700');
    
    if (button.classList.contains('text-red-500')) {
        showNotification('به علاقه‌مندی‌ها اضافه شد');
        addToFavorites();
    } else {
        showNotification('از علاقه‌مندی‌ها حذف شد');
    }
}

function addToFavorites() {
    const favoriteBadge = document.querySelector('.fa-heart').parentElement.querySelector('span');
    const currentCount = parseInt(favoriteBadge.textContent);
    favoriteBadge.textContent = currentCount + 1;
}

// Compare Management
function addToCompare() {
    showNotification('محصول به لیست مقایسه اضافه شد');
}

// Utility Functions
function formatPrice(price) {
    return price.toLocaleString('fa-IR');
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-primary text-white px-6 py-3 rounded-lg shadow-lg z-40 animate-fade-in';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('animate-fade-out');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Add styles for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(20px);
        }
    }
    
    @keyframes modalSlide {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
    
    .animate-fade-out {
        animation: fadeOut 0.3s ease-out;
    }
    
    .animate-modal {
        animation: modalSlide 0.3s ease-out;
    }
    
    .owl-carousel .owl-nav button {
        background: rgba(217, 70, 239, 0.1) !important;
        color: #d946ef !important;
        border-radius: 50%;
        width: 45px !important;
        height: 45px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.3s ease;
    }
    
    .owl-carousel .owl-nav button:hover {
        background: #d946ef !important;
        color: white !important;
    }
    
    .owl-carousel .owl-dots .owl-dot {
        background: #e5e7eb !important;
        border-radius: 50%;
        margin: 5px;
    }
    
    .owl-carousel .owl-dots .owl-dot.active {
        background: #d946ef !important;
    }
    
    /* RTL Support for Owl Carousel */
    .owl-carousel.owl-rtl {
        direction: rtl;
    }
    
    .owl-carousel.owl-rtl .owl-item {
        direction: rtl;
    }
`;
document.head.appendChild(style);

// Initialize RTL for Owl Carousel
$(document).ready(function() {
    $(".owl-carousel").addClass("owl-rtl");
});

// Product Filter (Mobile optimization)
function filterProducts(category) {
    showNotification(`نمایش محصولات: ${category}`);
}

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#' && document.querySelector(href)) {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Lazy loading for images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// Search functionality
document.querySelectorAll('input[placeholder*="جستجو"]').forEach(input => {
    input.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const searchResults = document.getElementById('searchResults');
        
        if (searchTerm.length < 2) {
            searchResults.innerHTML = '<p class="text-gray-500 text-center py-8">شروع به تایپ کنید تا نتایج نمایش داده شود</p>';
            return;
        }
        
        // Mock search results
        const results = [
            { name: 'پیراهن مجلسی', price: 289000 },
            { name: 'بلوز کاژوال', price: 189000 },
            { name: 'دامن طرح‌دار', price: 349000 },
            { name: 'شلوار جین', price: 429000 }
        ].filter(product => product.name.includes(searchTerm));
        
        if (results.length === 0) {
            searchResults.innerHTML = '<p class="text-gray-500 text-center py-8">نتیجه‌ای یافت نشد</p>';
            return;
        }
        
        let html = '';
        results.forEach(result => {
            html += `
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <div>
                        <h3 class="font-bold text-gray-800">${result.name}</h3>
                        <p class="text-primary font-bold">${formatPrice(result.price)}</p>
                    </div>
                    <i class="fas fa-arrow-left text-primary"></i>
                </div>
            `;
        });
        searchResults.innerHTML = html;
    });
});

// Newsletter subscription
document.querySelectorAll('input[type="email"]').forEach(input => {
    const parent = input.closest('div');
    if (parent && parent.querySelector('button')) {
        parent.querySelector('button').addEventListener('click', function() {
            const email = input.value;
            if (email && email.includes('@')) {
                showNotification('تشکر! شما به خبرنامه ما عضو شدید');
                input.value = '';
            } else {
                showNotification('لطفاً یک ایمیل معتبر وارد کنید');
            }
        });
    }
});

// Form validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        showNotification('فرم شما با موفقیت ارسال شد');
        form.reset();
    });
});
