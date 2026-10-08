<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shaw Supply - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Correct DataTables v1.13.6 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
    <style>
        .summary-box {
          background: #f1f3f7;
          padding: 20px;
          border-radius: 10px;
          text-align: center;
          transition: 0.3s;
        }
        .summary-box:hover {
          background: #e7ebf3;
        }
        .summary-box h4 {
          color: #2c7be5;
          margin-bottom: 10px;
        }
        .dt-buttons {
            display: none;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" class="bi bi-grid-fill me-2" viewBox="0 0 16 16">
                    <path d="M1 2a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2zm6 0a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V2zm0 6a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8zm-6 0a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V8z"
                    />
                </svg>
                Shaw Supply
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Dashboard</a>
                    </li>
                </ul>
                <span class="navbar-text text-white small">
                    Store: <strong>{{ $shop }}</strong>
                </span>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="row g-3 mb-5">
            <div class="col-md-3">
                <div class="summary-box">
                    <h4>Today</h4>
                    <p>{{ @$summary['today'] }}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-box">
                    <h4>This Week</h4>
                    <p>{{ @$summary['week'] }}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-box">
                    <h4>This Month</h4>
                    <p>{{ @$summary['month'] }}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-box">
                    <h4>Lifetime</h4>
                    <p>{{ @$summary['lifetime'] }}</p>
                </div>
            </div>
        </div>

        <!-- Table Listing -->
        <div class="table-responsive">
            <table id="quoteTable" class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Variant Price</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Quantity</th>
                        <!--<th>Comments</th>-->
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotes as $index => $quote)
                    <tr>
                        <td></td>
                        <td>{{ $quote->product_title }}</td>
                        <td>{{ $quote->selected_variant }}</td>
                        <td>
                          @if(filled($quote->variant_price))
                            ${{ number_format((float)$quote->variant_price, 2) }}
                          @endif
                        </td>
                        <td>{{ $quote->first_name }} {{ $quote->last_name }}</td>
                        <td>{{ $quote->email }}</td>
                        <td>
                          @if(filled($quote->phone))
                            {{ $quote->phone }}
                          @endif
                        </td>
                        <td>{{ $quote->quantity }}</td>
                        <td>{{ $quote->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No quotes found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <footer class="mt-5 py-4 border-top bg-light">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
            <p class="mb-2 mb-md-0 text-secondary small">
                &copy; {{ date('Y') }} <strong>Shaw Supply</strong>. All rights reserved.
            </p>
            <p class="mb-0 small text-secondary">Need support? Please send mail at <a href="javascript:void(0);" class="text-decoration-none text-primary">info.cyberbells@gmail.com</a></p>
        </div>
    </footer>
    <script>
        // Get shop from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const shop = urlParams.get('shop') || '{{ session("shopify_shop") }}';
        const apiKey = '{{ config("shopify.api_key") }}';
        if (shop && apiKey) {
            // Initialize Shopify App Bridge
            const AppBridge = window['app-bridge'];
            const createApp = AppBridge.default;
            const app = createApp({
                apiKey: apiKey,
                host: new URLSearchParams(window.location.search).get('host') || btoa(shop + '/admin'),
                forceRedirect: true
            });
            console.log('Shopify App Bridge initialized for shop:', shop);
        }
    </script>
    <!-- Required JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        $(document).ready(function () {

            const table = $('#quoteTable').DataTable({

                dom: 'Bfrtip',

                buttons: [{

                  extend: 'excel',

                  text: 'Export Data to Excel',

                  className: 'exportButton'

                }],

                pageLength: 10,

                order: [[5, 'desc']], // order by created_at column

                columnDefs: [

                    {

                        targets: 0,

                        searchable: false,

                        orderable: false,

                    },

                ],

            });

    

            // Auto-increment index column

            table.on('order.dt search.dt', function () {

                let i = 1;

                table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {

                    this.data(i++);

                });

            }).draw();

        });

    </script>
</body>

</html>