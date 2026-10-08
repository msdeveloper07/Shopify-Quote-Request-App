<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shaw Supply - Admin Dashboard</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="{{ asset('admin_shopify/style.css') }}">
    <!-- Shopify App Bridge -->
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-quote-left me-2"></i>
                Shaw Supply - Quote Manager
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text text-white">
                            <i class="fas fa-store me-1"></i> {{ $shop }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-sm-2">
                <div class="summary-box today">
                    <h4><i class="fas fa-calendar-day"></i> Today</h4>
                    <div class="number">{{ $summary['today'] }}</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-2">
                <div class="summary-box week">
                    <h4><i class="fas fa-calendar-week"></i> This Week</h4>
                    <div class="number">{{ $summary['week'] }}</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-2">
                <div class="summary-box month">
                    <h4><i class="fas fa-calendar-alt"></i> This Month</h4>
                    <div class="number">{{ $summary['month'] }}</div>
                </div>
            </div>
            <div class="col-md-2 col-sm-2">
                <div class="summary-box lifetime">
                    <h4><i class="fas fa-infinity"></i> Lifetime</h4>
                    <div class="number">{{ $summary['lifetime'] }}</div>
                </div>
            </div>
        <!-- </div> -->

        <!-- Status Summary -->
        <!-- <div class="row g-3 mb-4"> -->
            <div class="col-md-2 col-sm-2">
                <div class="summary-box pending">
                    <h4><i class="fas fa-clock"></i> Pending</h4>
                    <div class="number">{{ $summary['pending'] }}</div>
                </div>
            </div>
            <!-- <div class="col-md-2 col-sm-2">
                <div class="summary-box in-progress">
                    <h4><i class="fas fa-spinner"></i> In Progress</h4>
                    <div class="number">{{ $summary['in_progress'] }}</div>
                </div>
            </div> -->
            <div class="col-md-2 col-sm-2">
                <div class="summary-box sent">
                    <h4><i class="fas fa-paper-plane"></i> Quotation Sent</h4>
                    <div class="number">{{ $summary['quotation_sent'] }}</div>
                </div>
            </div>
        </div>

        <!-- Quotes Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list"></i> All Quotes</h5>
                    <button class="btn btn-sm btn-primary" onclick="refreshTable()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Status Filter -->
                <div class="status-filter">
                    <button class="btn btn-sm btn-outline-primary active" onclick="filterByStatus('all')">
                        All
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="filterByStatus('pending')">
                        Pending
                    </button>
                    <!-- <button class="btn btn-sm btn-outline-info" onclick="filterByStatus('in_progress')">
                        In Progress
                    </button> -->
                    <button class="btn btn-sm btn-outline-primary" onclick="filterByStatus('quotation_sent')">
                        Quotation Sent
                    </button>
                    <!-- <button class="btn btn-sm btn-outline-success" onclick="filterByStatus('completed')">
                        Completed
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="filterByStatus('cancelled')">
                        Cancelled
                    </button> -->
                </div>

                <!-- DataTable -->
                <div class="table-responsive">
                    <table class="table table-hover" id="quoteTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Variant</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quotes as $quote)
                            <tr>
                                <td><strong>#{{ $quote->id }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($quote->product_image)
                                        <img src="{{ $quote->product_image }}" 
                                             alt="{{ $quote->product_title }}" 
                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px;">
                                        @endif
                                        <span>{{ Str::limit($quote->product_title, 30) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $quote->first_name }} {{ $quote->last_name }}</strong>
                                </td>
                                <td>
                                    <small>
                                        <i class="fas fa-envelope"></i> {{ $quote->email }}<br>
                                        <i class="fas fa-phone"></i> {{ $quote->phone }}
                                    </small>
                                </td>
                                <td>{{ $quote->variant_name ?? Str::limit($quote->selected_variant ?? 'N/A', 20) }}</td>
                                <td>
                                    @if($quote->variant_price)
                                        ${{ number_format($quote->variant_price, 2) }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $quote->quantity }}</td>
                                <td>{!! $quote->status_badge !!}</td>
                                <td>
                                    <small>{{ $quote->created_at->format('d M Y') }}<br>{{ $quote->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-info btn-action" 
                                                onclick="viewQuote({{ $quote->id }})" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success btn-action" 
                                                onclick="sendQuotation({{ $quote->id }})" 
                                                title="Send Quotation">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                        <!-- <button class="btn btn-sm btn-primary btn-action" 
                                                onclick="updateStatus({{ $quote->id }})" 
                                                title="Update Status">
                                            <i class="fas fa-edit"></i>
                                        </button> -->
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No quotes found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- View Quote Modal -->
    <div class="modal fade" id="viewQuoteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice"></i> Quote Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="loading-spinner" id="viewLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="quoteDetails"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        <input type="hidden" id="status_quote_id">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="quote_status" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="quotation_sent">Quotation Sent</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea class="form-control" id="status_admin_notes" rows="3" placeholder="Add internal notes..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Send Quotation Modal -->
    <div class="modal fade" id="sendQuotationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-paper-plane"></i> Send Quotation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Fill in the quoted price and send the quotation to the customer
                    </div>
                    <form id="quotationForm">
                        <input type="hidden" id="quotation_quote_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Quoted Price ($) *</label>
                            <input type="number" class="form-control" id="quoted_price" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Message to Customer</label>
                            <textarea class="form-control" id="quotation_message" rows="4" placeholder="Add a personalized message..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Internal Notes</label>
                            <textarea class="form-control" id="quotation_admin_notes" rows="2" placeholder="Internal notes (not visible to customer)"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-paper-plane"></i> Send Quotation Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastArea"></div>

    <style>
        .toast {
            border-radius: 10px;
            font-size: 14px;
        }
    </style>

    <!-- Footer -->
    <footer class="bg-light border-top mt-5 py-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-0">© {{ date('Y') }} <strong>Shaw Supply</strong>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        Need support? <a href="mailto:info.cyberbells@gmail.com">info.cyberbells@gmail.com</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        const SHOP = "{{ $shop }}";
        let dataTable;

        $(document).ready(function() {
            // Initialize DataTable
            dataTable = $('#quoteTable').DataTable({
                // order: [[0, 'desc']],
                ordering: false,
                pageLength: 20,
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'print'],
                responsive: true,
                language: {
                    emptyTable: "No quotes available"
                }
            });

            // Initialize Shopify App Bridge
            if (window['app-bridge']) {
                const AppBridge = window['app-bridge'];
                const createApp = AppBridge.default;
                
                try {
                    const app = createApp({
                        apiKey: "{{ config('shopify.api_key') }}",
                        host: new URLSearchParams(window.location.search).get('host') || btoa(SHOP + '/admin'),
                        forceRedirect: true
                    });
                    console.log('Shopify App Bridge initialized');
                } catch (error) {
                    console.error('App Bridge error:', error);
                }
            }
        });

        // Filter by status
        function filterByStatus(status) {
            // Update active button
            $('.status-filter .btn').removeClass('active');
            event.target.classList.add('active');
            
            if (status === 'all') {
                window.location.href = `/dashboard?shop=${SHOP}`;
            } else {
                window.location.href = `/dashboard?shop=${SHOP}&status=${status}`;
            }
        }

        // View quote details
        async function viewQuote(quoteId) {
            console.log('Running!!');
            const modal = new bootstrap.Modal(document.getElementById('viewQuoteModal'));
            const loading = document.getElementById('viewLoading');
            const details = document.getElementById('quoteDetails');

            // Reset modal state
            loading.classList.add('active');
            details.innerHTML = '';
            modal.show();

            const APP_URL = "{{ rtrim(config('app.url'), '/') }}";
            const url = `${APP_URL}/quotes/${quoteId}?shop=${SHOP}`;
            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                xhrFields: {
                    withCredentials: true
                },

                success: function (data) {
                    loading.classList.remove('active');
                    if (data.success) {
                        renderQuoteDetails(data.quote);
                    } else {
                        details.innerHTML =`<div class="alert alert-warning">Failed: ${data.message}</div>`;
                    }
                },

                error: function (xhr, status, error) {
                    loading.classList.remove('active');
                    details.innerHTML ='<div class="alert alert-danger">Failed to load quote details</div>';
                    console.error("AJAX ERROR:", error);
                    console.log("RESPONSE TEXT:", xhr.responseText);
                }
            });
        }


        function renderQuoteDetails(quote) {
            const details = document.getElementById('quoteDetails');
            let html = `
                <div class="text-center mb-4">
                    <h4>Quote #${quote.id}</h4>
                    ${quote.status}
                </div>
            `;

            if (quote.product_image) {
                html += `
                    <div class="text-center mb-4">
                        <img src="${quote.product_image}" class="product-image-modal" alt="${quote.product_title}">
                    </div>
                `;
            }

            html += `
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-group">
                            <strong><i class="fas fa-box"></i> Product Information</strong>
                            <p class="mb-1">${quote.product_title}</p>
                            ${quote.variant_name ? `<small class="text-muted">Variant: ${quote.variant_name}</small>` : (quote.selected_variant ? `<small class="text-muted">Variant ID: ${quote.selected_variant}</small>` : '')}                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-group">
                            <strong><i class="fas fa-user"></i> Customer Information</strong>
                            <p class="mb-1">${quote.first_name} ${quote.last_name}</p>
                            <small class="text-muted">
                                <i class="fas fa-envelope"></i> ${quote.email}<br>
                                <i class="fas fa-phone"></i> ${quote.phone}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="detail-group">
                            <strong><i class="fas fa-tag"></i> Price</strong>
                            <p class="mb-0">${quote.variant_price ? '$' + parseFloat(quote.variant_price).toFixed(2) : 'N/A'}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <strong><i class="fas fa-hashtag"></i> Quantity</strong>
                            <p class="mb-0">${quote.quantity}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <strong><i class="fas fa-calculator"></i> Total</strong>
                            <p class="mb-0">${quote.variant_price ? '$' + (parseFloat(quote.variant_price) * quote.quantity).toFixed(2) : 'N/A'}</p>
                        </div>
                    </div>
                </div>

                ${quote.quoted_price ? `
                <div class="detail-group bg-success text-white">
                    <strong><i class="fas fa-dollar-sign"></i> Quoted Price</strong>
                    <h3 class="mb-0">$${parseFloat(quote.quoted_price).toFixed(2)}</h3>
                    ${quote.quantity > 1 ? `<small>Total: $${(parseFloat(quote.quoted_price) * quote.quantity).toFixed(2)}</small>` : ''}
                </div>
                ` : ''}

                ${quote.comments ? `
                <div class="detail-group">
                    <strong><i class="fas fa-comment"></i> Customer Comments</strong>
                    <p class="mb-0">${quote.comments}</p>
                </div>
                ` : ''}

                ${quote.admin_notes ? `
                <div class="detail-group">
                    <strong><i class="fas fa-sticky-note"></i> Admin Notes</strong>
                    <p class="mb-0">${quote.admin_notes}</p>
                </div>
                ` : ''}

                <div class="detail-group">
                    <strong><i class="fas fa-clock"></i> Timeline</strong>
                    <p class="mb-1"><small>Submitted: ${formatDate(quote.created_at)}</small></p>
                    ${quote.quotation_sent_at ? `<p class="mb-0"><small>Quotation Sent: ${formatDate(quote.quotation_sent_at)}</small></p>` : ''}
                </div>

                ${quote.product_url ? `
                <div class="text-center mt-3">
                    <a href="${quote.product_url}" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i> View Product
                    </a>
                </div>
                ` : ''}
            `;

            details.innerHTML = html;
        }

        // Update status
        function updateStatus(quoteId) {
            document.getElementById('status_quote_id').value = quoteId;
            const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            modal.show();
        }

        document.getElementById('statusForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const quoteId = document.getElementById('status_quote_id').value;
            const status = document.getElementById('quote_status').value;
            const notes = document.getElementById('status_admin_notes').value;

            const APP_URL = "{{ rtrim(config('app.url'), '/') }}";
            const url = `${APP_URL}/quotes/${quoteId}/update-status?shop=${SHOP}`;

            $.ajax({
                url: url,
                type: "POST",
                dataType: "json",
                contentType: "application/json",
                xhrFields: {
                    withCredentials: true
                },
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                data: JSON.stringify({
                    status: status,
                    admin_notes: notes
                }),

                success: function (data) {
                    if (data.success) {
                        showToast("Status updated successfully!", "success");
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showToast("Failed to update status: " + data.message, "error");
                    }
                },

                error: function (xhr, status, error) {
                    console.error("AJAX ERROR:", error);
                    console.log("RESPONSE TEXT:", xhr.responseText);
                    showToast("An error occurred while updating status", "error");
                }
            });
        });



        // Send quotation
        function sendQuotation(quoteId) {
            document.getElementById('quotation_quote_id').value = quoteId;
            const modal = new bootstrap.Modal(document.getElementById('sendQuotationModal'));
            modal.show();
        }

        // document.getElementById('quotationForm').addEventListener('submit', function(e) {
        //     e.preventDefault();

        //     const quoteId = document.getElementById('quotation_quote_id').value;
        //     const price = document.getElementById('quoted_price').value;
        //     const message = document.getElementById('quotation_message').value;
        //     const notes = document.getElementById('quotation_admin_notes').value;
        //     const APP_URL = "{{ rtrim(config('app.url'), '/') }}";
        //     const url = `${APP_URL}/quotes/${quoteId}/send-quotation?shop=${SHOP}`;
        //     Swal.fire({
        //         title: "Send Quotation?",
        //         text: "Are you sure you want to send this quotation to the customer?",
        //         icon: "warning",
        //         showCancelButton: true,
        //         confirmButtonColor: "#3085d6",
        //         cancelButtonColor: "#d33",
        //         confirmButtonText: "Yes, send it!",
        //         cancelButtonText: "Cancel"
        //     }).then((result) => {
        //         if (!result.isConfirmed) {
        //             return;
        //         }

        //         $.ajax({
        //             url: url,
        //             type: "POST",
        //             dataType: "json",
        //             contentType: "application/json",
        //             xhrFields: {
        //                 withCredentials: true
        //             },
        //             headers: {
        //                 "X-CSRF-TOKEN": "{{ csrf_token() }}"
        //             },
        //             data: JSON.stringify({
        //                 quoted_price: price,
        //                 message: message,
        //                 admin_notes: notes
        //             }),

        //             success: function (data) {
        //                 if (data.success) {
        //                     showToast(data.message, "success");
        //                     setTimeout(() => location.reload(), 2000);
        //                 } else {
        //                     showToast("Failed to send quotation: " + data.message, "error");
        //                 }
        //             },
        //             error: function (xhr, status, error) {
        //                 showToast("An error occurred while sending the quotation", "error");
        //             }
        //         });
        //     });
        // });

        document.getElementById('quotationForm').addEventListener('submit', function(e) {
        e.preventDefault();

            const quoteId = document.getElementById('quotation_quote_id').value;
            const price = document.getElementById('quoted_price').value;
            const message = document.getElementById('quotation_message').value;
            const notes = document.getElementById('quotation_admin_notes').value;
            const APP_URL = "{{ rtrim(config('app.url'), '/') }}";
            const url = `${APP_URL}/quotes/${quoteId}/send-quotation?shop=${SHOP}`;
            
            Swal.fire({
                title: "Send Quotation?",
                text: "Are you sure you want to send this quotation to the customer?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, send it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                // Get the submit button
                const submitBtn = document.querySelector('#quotationForm button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: "json",
                    contentType: "application/json",
                    xhrFields: {
                        withCredentials: true
                    },
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    data: JSON.stringify({
                        quoted_price: price,
                        message: message,
                        admin_notes: notes
                    }),

                    success: function (data) {
                        if (data.success) {
                            // Keep loading state and show success
                            submitBtn.innerHTML = '<i class="fas fa-check"></i> Sent Successfully!';
                            showToast(data.message, "success");
                            
                            // Close modal and reload after 2 seconds
                            setTimeout(() => {
                                bootstrap.Modal.getInstance(document.getElementById('sendQuotationModal')).hide();
                                location.reload();
                            }, 2000);
                        } else {
                            // Restore button on failure
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                            showToast("Failed to send quotation: " + data.message, "error");
                        }
                    },
                    
                    error: function (xhr, status, error) {
                        // Restore button on error
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        showToast("An error occurred while sending the quotation", "error");
                        console.error("AJAX ERROR:", error);
                        console.log("RESPONSE TEXT:", xhr.responseText);
                    }
                });
            });
        });


        // Refresh table
        function refreshTable() {
            location.reload();
        }

        // Format date helper
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function showToast(message, type = "success") {
            let bgClass = type === "success" ? "bg-success text-white" : "bg-danger text-white";
            const toastId = "toast_" + Date.now();
            let toastHTML = `
                <div id="${toastId}" class="toast align-items-center ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;

            $("#toastArea").append(toastHTML);
            let toastEl = document.getElementById(toastId);
            let toast = new bootstrap.Toast(toastEl);
            toast.show();
            // Remove after hidden
            toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
        }

    </script>
    <script src="{{ asset('admin_shopify/script.js') }}"></script>
</body>
</html>