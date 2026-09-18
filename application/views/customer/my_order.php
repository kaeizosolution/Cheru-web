<div class="body_content_wrapper position-relative">
            <!-- Inner Page Breadcrumb -->
            <section class="inner_page_breadcrumb">
              <div class="container">
                <div class="row">
                  <div class="col-xl-6">
                    <div class="breadcrumb_content">
                      <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index1.html">Home</a></li>
                        <li class="breadcrumb-item"><a href="/page-account-dashboard.html">My Account</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                          <a href="#">My Orders</a>
                        </li>
                      </ol>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          
            <!-- My Account – Orders List -->
            <section class="account-page pt40 pb60">
              <div class="container">
                <div class="row">
                  <!-- Sidebar -->
                  <aside class="col-lg-3 mb30">
                    <div class="account-sidebar">
                      <div class="account-user-block d-flex align-items-center mb20">
                        <div class="account-user-avatar">
                          <span class="fa fa-user"></span>
                        </div>
                        <div>
                          <p class="account-hello mb0">Hello,</p>
                          <h6 class="account-name mb0">Varun Tanwar</h6>
                        </div>
                      </div>
          
                      <ul class="account-nav list-unstyled mb0">
                        <li class="account-nav-item">
                          <a href="page-account-dashboard.html">
                            <span class="fa fa-home me-2"></span> Dashboard
                          </a>
                        </li>
                        <li class="account-nav-item active">
                          <a href="page-account-orders.html">
                            <span class="fa fa-shopping-bag me-2"></span> My Orders
                          </a>
                        </li>
                        <li class="account-nav-item">
                          <a href="page-account-address.html">
                            <span class="fa fa-map-marker-alt me-2"></span> Addresses
                          </a>
                        </li>
                        <li class="account-nav-item">
                          <a href="page-account-details.html">
                            <span class="fa fa-user-cog me-2"></span> Account Details
                          </a>
                        </li>
                        <li class="account-nav-item">
                          <a href="page-account-wishlist.html">
                            <span class="fa fa-heart me-2"></span> Wishlist
                          </a>
                        </li>
                        <li class="account-nav-item logout">
                          <a href="page-login.html">
                            <span class="fa fa-sign-out-alt me-2"></span> Logout
                          </a>
                        </li>
                      </ul>
                    </div>
                  </aside>
          
                  <!-- Main content -->
                  <main class="col-lg-9">
                    <!-- Header + filter -->
                    <div class="account-panel mb20">
                      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                          <h4 class="mb5">My Orders</h4>
                          <p class="text-muted mb0 fz13">
                            View, track or manage all your orders in one place.
                          </p>
                        </div>
                        <div>
                          <ul class="orders-filter-list mb0">
                            <li><button class="orders-filter-pill active">All</button></li>
                            <li><button class="orders-filter-pill">Active</button></li>
                            <li><button class="orders-filter-pill">Completed</button></li>
                            <li><button class="orders-filter-pill">Cancelled</button></li>
                          </ul>
                        </div>
                      </div>
                    </div>
          
                    <!-- Orders table -->
                    <div class="account-panel">
                      <div class="table-responsive">
                        <table class="table account-orders-table mb0">
                          <thead>
                            <tr>
                              <th>Order</th>
                              <th>Date</th>
                              <th>Status</th>
                              <th>Items</th>
                              <th>Total</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            <!-- Order row 1 -->
                            <tr>
                              <td>#CH-2045879</td>
                              <td>26 Nov 2025</td>
                              <td>
                                <span class="order-status badge-soft-green">Confirmed</span>
                              </td>
                              <td>3 items</td>
                              <td>$225.98</td>
                              <td class="text-end">
                                <a href="page-account-order-details.html" class="text-thm fz13 me-2">
                                  View details
                                </a>
                                <a href="#" class="text-muted fz13">
                                  Track
                                </a>
                              </td>
                            </tr>
          
                            <!-- Order row 2 -->
                            <tr>
                              <td>#CH-2045321</td>
                              <td>18 Nov 2025</td>
                              <td>
                                <span class="order-status badge-soft-purple">Shipped</span>
                              </td>
                              <td>1 item</td>
                              <td>$149.00</td>
                              <td class="text-end">
                                <a href="page-account-order-details.html" class="text-thm fz13 me-2">
                                  View details
                                </a>
                                <a href="#" class="text-muted fz13">
                                  Track
                                </a>
                              </td>
                            </tr>
          
                            <!-- Order row 3 -->
                            <tr>
                              <td>#CH-2045100</td>
                              <td>08 Nov 2025</td>
                              <td>
                                <span class="order-status badge-soft-gray">Delivered</span>
                              </td>
                              <td>2 items</td>
                              <td>$89.99</td>
                              <td class="text-end">
                                <a href="page-account-order-details.html" class="text-thm fz13 me-2">
                                  View details
                                </a>
                                <a href="#" class="text-muted fz13">
                                  Invoice
                                </a>
                              </td>
                            </tr>
          
                            <!-- Order row 4 -->
                            <tr>
                              <td>#CH-2044900</td>
                              <td>29 Oct 2025</td>
                              <td>
                                <span class="order-status badge-soft-red">Cancelled</span>
                              </td>
                              <td>1 item</td>
                              <td>$39.00</td>
                              <td class="text-end">
                                <a href="page-account-order-details.html" class="text-thm fz13 me-2">
                                  View details
                                </a>
                                <a href="#" class="text-muted fz13">
                                  Re-order
                                </a>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
          
                      <!-- Pagination (static for now) -->
                      <div class="d-flex justify-content-between align-items-center mt15">
                        <p class="mb0 fz13 text-muted">Showing 1–4 of 12 orders</p>
                        <ul class="pagination mb0">
                          <li class="page-item disabled"><a class="page-link" href="#">«</a></li>
                          <li class="page-item active"><a class="page-link" href="#">1</a></li>
                          <li class="page-item"><a class="page-link" href="#">2</a></li>
                          <li class="page-item"><a class="page-link" href="#">3</a></li>
                          <li class="page-item"><a class="page-link" href="#">»</a></li>
                        </ul>
                      </div>
                    </div>
                  </main>
                </div>
              </div>
            </section>
          
            <!-- Footer (reuse existing footer markup here) -->
          </div>