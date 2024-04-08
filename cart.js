document.addEventListener('DOMContentLoaded', restoreCart);

function addToCart(button) {
    const pid = button.getAttribute('data-pid');
    let cart = getCart();

    if (cart[pid]) {
        cart[pid].quantity += 1; // Increment quantity if product already in cart
        updateCart(cart);
    } else {
        // Fetch product details over AJAX with error handling
        fetchProductDetails(pid).then(product => {
            cart[pid] = {
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1
            };
            updateCart(cart);
        }).catch(error => {
            displayError('Failed to fetch product details. Please try again later.');
        });
    }
}

function fetchProductDetails(pid) {
    return new Promise((resolve, reject) => {
        fetch('getProductDetails.php?pid=' + pid)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data && !data.error) {
                    resolve(data);
                } else {
                    reject('Error fetching product details');
                }
            })
            .catch(error => {
                reject(error);
            });
    });
}

function getCart() {
    return JSON.parse(localStorage.getItem('shoppingCart')) || {};
}

function updateCart(cart) {
    localStorage.setItem('shoppingCart', JSON.stringify(cart));
    renderCart(cart);
}

function renderCart(cart) {
  const cartItemsContainer = document.getElementById('cart-items');
  cartItemsContainer.innerHTML = ''; // Clear the cart

  let total = 0;

  for (let pid in cart) {
      const product = cart[pid];
      total += product.price * product.quantity;

      const item = document.createElement('div');
      item.className = 'cart-item';
      item.innerHTML = `
          <span>${product.name}</span>
          <span>$${product.price.toFixed(2)}</span>
          <button onclick="decrementQuantity('${pid}')">-</button>
          <input type="number" value="${product.quantity}" min="1" class="quantity-input" onchange="changeQuantity(this, '${pid}')">
          <button onclick="incrementQuantity('${pid}')">+</button>
          <button onclick="removeFromCart('${pid}')">Remove</button>
      `;
      cartItemsContainer.appendChild(item);
  }

  // Update total
  const totalElement = document.getElementById('cart-total');
  totalElement.textContent = `Total: $${total.toFixed(2)}`;

  // Update cart count
  const cartCount = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
  document.getElementById('cart-count').textContent = cartCount;
  document.getElementById('cart-count-dropdown').textContent = cartCount;
}

function changeQuantity(input, pid) {
    let cart = getCart();
    const newQuantity = parseInt(input.value);
    
    if (isNaN(newQuantity) || newQuantity <= 0) {
        input.value = cart[pid].quantity; // Reset to old value
        displayError('Quantity must be a positive number.');
        return;
    }

    cart[pid].quantity = newQuantity;
    updateCart(cart);
}

function removeFromCart(pid) {
    let cart = getCart();
    delete cart[pid];
    updateCart(cart);
}

function restoreCart() {
    const cart = getCart();
    renderCart(cart);
}

function displayError(message) {
  const errorElement = document.getElementById('error-message');
  errorElement.textContent = message;
  errorElement.style.display = 'block'; // Show the error message

  // Optionally hide the message after some time
  setTimeout(() => {
      errorElement.style.display = 'none';
  }, 5000);
  
}

function incrementQuantity(pid) {
  let cart = getCart();
  if (cart[pid]) {
      cart[pid].quantity += 1;
      updateCart(cart);
  }
}

function decrementQuantity(pid) {
  let cart = getCart();
  if (cart[pid] && cart[pid].quantity > 1) {
      cart[pid].quantity -= 1;
      updateCart(cart);
  }
}