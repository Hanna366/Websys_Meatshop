@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Product Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportProducts()">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProductModal">
                <i class="fas fa-plus me-1"></i> Add Product
            </button>
        </div>
    </div>

    <!-- Product Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">245</div>
                            <div class="text-xs text-gray-500">Active items</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                In Stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">198</div>
                            <div class="text-xs text-gray-500">Available</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Low Stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                            <div class="text-xs text-gray-500">Need reorder</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Categories</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                            <div class="text-xs text-gray-500">Product types</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-tabs card-header-tabs" id="categoryTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">All Products</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="prime-tab" data-bs-toggle="tab" data-bs-target="#prime" type="button">Prime Grade</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="premium-tab" data-bs-toggle="tab" data-bs-target="#premium" type="button">Premium Grade</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="select-tab" data-bs-toggle="tab" data-bs-target="#select" type="button">Select Grade</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="byproduct-tab" data-bs-toggle="tab" data-bs-target="#byproduct" type="button">Byproducts</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <!-- Search and Filter Bar -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search products..." id="productSearch">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="stockFilter">
                        <option value="">All Stock Levels</option>
                        <option value="instock">In Stock</option>
                        <option value="lowstock">Low Stock</option>
                        <option value="outofstock">Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="sortBy">
                        <option value="name">Sort by Name</option>
                        <option value="price">Sort by Price</option>
                        <option value="stock">Sort by Stock</option>
                        <option value="created">Sort by Date Added</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="tab-content" id="categoryTabContent">
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    <div class="row" id="productsGrid">
                        <!-- Prime Grade Products -->
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 product-card">
                                <img src="https://picsum.photos/seed/primerib/300/200.jpg" class="card-img-top" alt="Prime Rib Steak">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Prime Rib Steak</h5>
                                    <p class="card-text text-muted small">Premium cut, perfect for roasting</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger">Prime</span>
                                        <span class="badge bg-success">In Stock</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">₱2,870/kg</span>
                                            <small class="text-muted">25 kg available</small>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(1)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewProduct(1)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addToCart(1)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 product-card">
                                <img src="https://picsum.photos/seed/ribeye/300/200.jpg" class="card-img-top" alt="Ribeye">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Ribeye</h5>
                                    <p class="card-text text-muted small">Marbled perfection for grilling</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger">Prime</span>
                                        <span class="badge bg-success">In Stock</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">₱3,570/kg</span>
                                            <small class="text-muted">18 kg available</small>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(2)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewProduct(2)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addToCart(2)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 product-card">
                                <img src="https://picsum.photos/seed/shortloin/300/200.jpg" class="card-img-top" alt="Shortloin Slab">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Shortloin Slab</h5>
                                    <p class="card-text text-muted small">Tender and flavorful cut</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger">Prime</span>
                                        <span class="badge bg-success">In Stock</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">₱2,670/kg</span>
                                            <small class="text-muted">22 kg available</small>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(3)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewProduct(3)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addToCart(3)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 product-card">
                                <img src="https://picsum.photos/seed/tenderloin/300/200.jpg" class="card-img-top" alt="Tenderloin">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Tenderloin</h5>
                                    <p class="card-text text-muted small">Most tender cut available</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger">Prime</span>
                                        <span class="badge bg-warning">Low Stock</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">₱4,020/kg</span>
                                            <small class="text-warning">8 kg available</small>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(4)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewProduct(4)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addToCart(4)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Premium Grade Products -->
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 product-card">
                                <img src="https://picsum.photos/seed/oysterblade/300/200.jpg" class="card-img-top" alt="Oyster Blade">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Oyster Blade</h5>
                                    <p class="card-text text-muted small">Rich flavor, great value</p>
                                    <div class="mb-2">
                                        <span class="badge bg-warning">Premium</span>
                                        <span class="badge bg-success">In Stock</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">₱1,720/kg</span>
                                            <small class="text-muted">35 kg available</small>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(5)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewProduct(5)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addToCart(5)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 product-card">
                                <img src="https://picsum.photos/seed/flatiron/300/200.jpg" class="card-img-top" alt="Flat Iron Steak">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Flat Iron Steak</h5>
                                    <p class="card-text text-muted small">Second most tender cut</p>
                                    <div class="mb-2">
                                        <span class="badge bg-warning">Premium</span>
                                        <span class="badge bg-success">In Stock</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">₱2,120/kg</span>
                                            <small class="text-muted">28 kg available</small>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(6)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewProduct(6)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addToCart(6)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 product-card">
                                <img src="https://picsum.photos/seed/brisket/300/200.jpg" class="card-img-top" alt="Brisket">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Brisket</h5>
                                    <p class="card-text text-muted small">Perfect for slow cooking</p>
                                    <div class="mb-2">
                                        <span class="badge bg-warning">Premium</span>
                                        <span class="badge bg-success">In Stock</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">₱980/kg</span>
                                            <small class="text-muted">42 kg available</small>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(7)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewProduct(7)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addToCart(7)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Select Grade Products -->
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 product-card">
                                <img src="https://picsum.photos/seed/chucktender/300/200.jpg" class="card-img-top" alt="Chuck Tender">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Chuck Tender</h5>
                                    <p class="card-text text-muted small">Budget-friendly cut</p>
                                    <div class="mb-2">
                                        <span class="badge bg-info">Select</span>
                                        <span class="badge bg-success">In Stock</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">₱770/kg</span>
                                            <small class="text-muted">55 kg available</small>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(8)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewProduct(8)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addToCart(8)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Byproducts -->
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 product-card">
                                <img src="https://picsum.photos/seed/bonemarrow/300/200.jpg" class="card-img-top" alt="Bone Marrow">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Bone Marrow</h5>
                                    <p class="card-text text-muted small">Rich and nutritious</p>
                                    <div class="mb-2">
                                        <span class="badge bg-secondary">Byproduct</span>
                                        <span class="badge bg-success">In Stock</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">₱440/kg</span>
                                            <small class="text-muted">15 kg available</small>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(9)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewProduct(9)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addToCart(9)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <nav aria-label="Products pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- New Product Modal -->
<div class="modal fade" id="newProductModal" tabindex="-1" aria-labelledby="newProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newProductModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newProductForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productCategory" class="form-label">Category</label>
                            <select class="form-select" id="productCategory" required>
                                <option value="">Select Grade</option>
                                <option value="prime">Prime Grade</option>
                                <option value="premium">Premium Grade</option>
                                <option value="select">Select Grade</option>
                                <option value="choice">Choice Grade</option>
                                <option value="byproduct">Byproduct</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="productPrice" class="form-label">Price per kg ($)</label>
                            <input type="number" class="form-control" id="productPrice" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productStock" class="form-label">Stock Quantity (kg)</label>
                            <input type="number" class="form-control" id="productStock" step="0.1" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="minStock" class="form-label">Min Stock Level (kg)</label>
                            <input type="number" class="form-control" id="minStock" step="0.1" min="0" value="5">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="productImage" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">Save Product</button>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.product-card img {
    height: 200px;
    object-fit: cover;
}
</style>

<script>
// Search functionality
document.getElementById('productSearch').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const cards = document.querySelectorAll('.product-card');
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.closest('.col-lg-3').style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Stock filter
document.getElementById('stockFilter').addEventListener('change', function() {
    const filter = this.value;
    const cards = document.querySelectorAll('.product-card');
    
    cards.forEach(card => {
        if(filter === '') {
            card.closest('.col-lg-3').style.display = '';
        } else {
            const badges = card.querySelectorAll('.badge');
            let hasMatchingBadge = false;
            
            badges.forEach(badge => {
                if(filter === 'instock' && badge.textContent === 'In Stock') hasMatchingBadge = true;
                if(filter === 'lowstock' && badge.textContent === 'Low Stock') hasMatchingBadge = true;
                if(filter === 'outofstock' && badge.textContent === 'Out of Stock') hasMatchingBadge = true;
            });
            
            card.closest('.col-lg-3').style.display = hasMatchingBadge ? '' : 'none';
        }
    });
});

// Sort functionality
document.getElementById('sortBy').addEventListener('change', function() {
    const sortBy = this.value;
    const grid = document.getElementById('productsGrid');
    const cards = Array.from(grid.querySelectorAll('.col-lg-3'));
    
    cards.sort((a, b) => {
        const aCard = a.querySelector('.product-card');
        const bCard = b.querySelector('.product-card');
        
        if(sortBy === 'name') {
            const aName = aCard.querySelector('.card-title').textContent;
            const bName = bCard.querySelector('.card-title').textContent;
            return aName.localeCompare(bName);
        } else if(sortBy === 'price') {
            const aPrice = parseFloat(aCard.querySelector('.h5').textContent.replace('$', ''));
            const bPrice = parseFloat(bCard.querySelector('.h5').textContent.replace('$', ''));
            return aPrice - bPrice;
        }
        // Add more sorting options as needed
        return 0;
    });
    
    cards.forEach(card => grid.appendChild(card));
});

// Product actions
function editProduct(id) {
    console.log('Editing product:', id);
    // Implement edit functionality
}

function viewProduct(id) {
    console.log('Viewing product:', id);
    // Implement view functionality
}

function addToCart(id) {
    console.log('Adding product to cart:', id);
    // Implement add to cart functionality
}

function restockProduct(id) {
    console.log('Restocking product:', id);
    // Implement restock functionality
}

function saveProduct() {
    console.log('Saving new product');
    // Implement save functionality
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('newProductModal'));
    modal.hide();
    document.getElementById('newProductForm').reset();
}

function exportProducts() {
    console.log('Exporting products');
    // Implement export functionality
}
</script>
@endsection
