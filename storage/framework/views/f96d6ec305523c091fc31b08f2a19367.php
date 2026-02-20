<?php $__env->startSection('title', 'Products - Meat Shop POS'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Meat Products & Byproducts</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Product
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary">
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
                            <td>Ribeye</td>
                            <td class="text-end fw-bold">$3,570</td>
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
                            <td>Shortloin Slab</td>
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
                            <td>Tenderloin</td>
                            <td class="text-end fw-bold">$4,020</td>
                            <td><span class="badge bg-danger">Beef</span></td>
                            <td><span class="badge bg-warning">Low Stock</span></td>
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
                            <td><span class="badge bg-secondary">Bones</span></td>
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
                            <td><span class="badge bg-secondary">Bones</span></td>
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
                            <td><span class="badge bg-secondary">Bones</span></td>
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
                            <td><span class="badge bg-warning">Byproduct</span></td>
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
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/products.blade.php ENDPATH**/ ?>