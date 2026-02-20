@extends('layouts.app')

@section('title', 'Products - Meat Shop POS')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Meat Products & Byproducts</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-primary" onclick="showAddProductModal()">
                    <i class="fas fa-plus me-1"></i> Add Product
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportProducts()">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Meat & Byproduct List with Prices</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Price (per kg)</th>
                            <th>Category</th>
                            <th>Stock Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Prime Rib Steak</td>
                            <td class="text-end fw-bold">₱2,870</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editProduct('Prime Rib Steak')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct('Prime Rib Steak')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Ribeye</td>
                            <td class="text-end fw-bold">₱3,570</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editProduct('Ribeye')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct('Ribeye')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Shortloin Slab</td>
                            <td class="text-end fw-bold">₱2,670</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editProduct('Shortloin Slab')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct('Shortloin Slab')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Tenderloin</td>
                            <td class="text-end fw-bold">₱4,020</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-warning">Low Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editProduct('Tenderloin')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct('Tenderloin')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Striploin</td>
                            <td class="text-end fw-bold">$2,870</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Porterhouse</td>
                            <td class="text-end fw-bold">$2,670</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>T-Bone</td>
                            <td class="text-end fw-bold">$2,470</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Oyster Blade</td>
                            <td class="text-end fw-bold">$1,720</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Flat Iron Steak</td>
                            <td class="text-end fw-bold">$2,120</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Brisket</td>
                            <td class="text-end fw-bold">$980</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Chuck Roll</td>
                            <td class="text-end fw-bold">$1,870</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Short Plate</td>
                            <td class="text-end fw-bold">$1,020</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Boneless Short Plate</td>
                            <td class="text-end fw-bold">$1,270</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Tenderloin Tip</td>
                            <td class="text-end fw-bold">$1,920</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Sirloin</td>
                            <td class="text-end fw-bold">$1,720</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Tri-tip</td>
                            <td class="text-end fw-bold">$1,720</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Flank Steak</td>
                            <td class="text-end fw-bold">$1,870</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Flank Whole</td>
                            <td class="text-end fw-bold">$885</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Chuck Tender</td>
                            <td class="text-end fw-bold">$770</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Bolar Blade</td>
                            <td class="text-end fw-bold">$1,060</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Short Ribs</td>
                            <td class="text-end fw-bold">$855</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Boneless Short Rib</td>
                            <td class="text-end fw-bold">$1,050</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Sirloin Tip</td>
                            <td class="text-end fw-bold">$970</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Top Round</td>
                            <td class="text-end fw-bold">$960</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Silverside</td>
                            <td class="text-end fw-bold">$880</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Neck Meat</td>
                            <td class="text-end fw-bold">$770</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Hump Roast</td>
                            <td class="text-end fw-bold">$770</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Shank BI</td>
                            <td class="text-end fw-bold">$620</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Eye Round</td>
                            <td class="text-end fw-bold">$770</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Shin/Shank Boneless</td>
                            <td class="text-end fw-bold">$670</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Neck Bones</td>
                            <td class="text-end fw-bold">$410</td>
                            <td><span class="badge bg-secondary">Byproducts</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Soup Bones</td>
                            <td class="text-end fw-bold">$220</td>
                            <td><span class="badge bg-secondary">Byproducts</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Bone Marrow</td>
                            <td class="text-end fw-bold">$440</td>
                            <td><span class="badge bg-secondary">Byproducts</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Fats</td>
                            <td class="text-end fw-bold">$340</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-success">In Stock</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
=======
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
                                <img src="https://picsum.photos/seed/beef-prime-rib-steak/300/200.jpg" class="card-img-top" alt="Prime Rib Steak">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Prime Rib Steak</h5>
                                    <p class="card-text text-muted small">Premium cut, perfect for roasting</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Beef - Premium cattle meat">Beef</span>
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
                                <img src="https://picsum.photos/seed/beef-ribeye-steak/300/200.jpg" class="card-img-top" alt="Ribeye">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Ribeye</h5>
                                    <p class="card-text text-muted small">Marbled perfection for grilling</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Beef - Premium cattle meat">Beef</span>
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
                                <img src="https://picsum.photos/seed/beef-shortloin-slab/300/200.jpg" class="card-img-top" alt="Shortloin Slab">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Shortloin Slab</h5>
                                    <p class="card-text text-muted small">Tender and flavorful cut</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Beef - Premium cattle meat">Beef</span>
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
                                <img src="https://picsum.photos/seed/beef-tenderloin-filet/300/200.jpg" class="card-img-top" alt="Tenderloin">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Tenderloin</h5>
                                    <p class="card-text text-muted small">Most tender cut available</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Beef - Premium cattle meat">Beef</span>
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
                                <img src="https://picsum.photos/seed/beef-oyster-blade/300/200.jpg" class="card-img-top" alt="Oyster Blade">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Oyster Blade</h5>
                                    <p class="card-text text-muted small">Rich flavor, great value</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Beef - Premium cattle meat">Beef</span>
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
                                <img src="https://picsum.photos/seed/beef-flat-iron-steak/300/200.jpg" class="card-img-top" alt="Flat Iron Steak">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Flat Iron Steak</h5>
                                    <p class="card-text text-muted small">Second most tender cut</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Beef - Premium cattle meat">Beef</span>
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
                                <img src="https://picsum.photos/seed/beef-brisket-cut/300/200.jpg" class="card-img-top" alt="Brisket">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Brisket</h5>
                                    <p class="card-text text-muted small">Perfect for slow cooking</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Beef - Premium cattle meat">Beef</span>
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
                                <img src="https://picsum.photos/seed/beef-chuck-tender/300/200.jpg" class="card-img-top" alt="Chuck Tender">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Chuck Tender</h5>
                                    <p class="card-text text-muted small">Budget-friendly cut</p>
                                    <div class="mb-2">
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Beef - Premium cattle meat">Beef</span>
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
                                <img src="https://picsum.photos/seed/beef-bone-marrow/300/200.jpg" class="card-img-top" alt="Bone Marrow">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Bone Marrow</h5>
                                    <p class="card-text text-muted small">Rich and nutritious</p>
                                    <div class="mb-2">
                                        <span class="badge bg-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="Byproducts - Animal organs and bones">Byproducts</span>
                                        <span class="badge bg-success" data-bs-toggle="tooltip" data-bs-placement="top" title="In Stock - Available for purchase">In Stock</span>
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
                                <option value="">Select Category</option>
                                <option value="beef">Beef</option>
                                <option value="pork">Pork</option>
                                <option value="chicken">Chicken</option>
                                <option value="turkey">Turkey</option>
                                <option value="goat">Goat</option>
                                <option value="lamb">Lamb</option>
                                <option value="byproduct">Byproducts</option>
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

<script>
// Initialize Bootstrap tooltips - Force refresh
document.addEventListener('DOMContentLoaded', function() {
    // Destroy any existing tooltips first
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        var tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
        if (tooltip) {
            tooltip.dispose();
        }
    });
    
    // Reinitialize tooltips
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { show: 0, hide: 0 },
            trigger: 'hover focus'
        });
    });
});

// Product Management Functions
function showAddProductModal() {
    document.getElementById('productModalLabel').textContent = 'Add New Product';
    document.getElementById('productForm').reset();
    new bootstrap.Modal(document.getElementById('productModal')).show();
}

function editProduct(productName) {
    document.getElementById('productModalLabel').textContent = 'Edit Product: ' + productName;
    // Simulate loading product data
    document.getElementById('productName').value = productName;
    document.getElementById('productPrice').value = '2500';
    document.getElementById('productCategory').value = 'beef';
    document.getElementById('productStock').value = '50';
    new bootstrap.Modal(document.getElementById('productModal')).show();
    showNotification('Editing product: ' + productName, 'info');
}

function deleteProduct(productName) {
    if (confirm('Are you sure you want to delete "' + productName + '"?')) {
        showNotification('Product "' + productName + '" deleted successfully!', 'success');
        // In real app, this would make an API call to delete the product
    }
}

function saveProduct() {
    const productName = document.getElementById('productName').value;
    if (!productName) {
        showNotification('Please enter a product name', 'warning');
        return;
    }
    
    showNotification('Product "' + productName + '" saved successfully!', 'success');
    bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
}

function exportProducts() {
    const products = [
        { name: 'Prime Rib Steak', price: '₱2,870', category: 'Beef', stock: 'In Stock' },
        { name: 'Ribeye', price: '₱3,570', category: 'Beef', stock: 'In Stock' },
        { name: 'Tenderloin', price: '₱4,020', category: 'Beef', stock: 'Low Stock' }
    ];
    
    const dataStr = JSON.stringify(products, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = 'products_export_' + new Date().toISOString().split('T')[0] + '.json';
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    
    showNotification('Products exported successfully!', 'success');
}

// Show notification function
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'warning' ? 'exclamation-triangle' : 'info-circle')} me-2"></i>
        ${message}
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endsection
