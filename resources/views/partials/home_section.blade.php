<section class="py-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <span class="badge bg-gradient-primary mb-3">Compliance-first HR platform</span>
        <h1 class="display-5 font-weight-bolder mb-3">Simplify your HRMS workflows</h1>
        <p class="lead text-secondary mb-4">
          Manage attendance, leave, payroll, and contracts seamlessly—stay audit-ready with automated alerts and clean reporting.
        </p>
        <div class="d-flex flex-wrap gap-2">
          @guest
            <a href="{{ route('register') }}" class="btn bg-gradient-primary mb-0">
              Get Started
            </a>
            <a href="{{ route('features') }}" class="btn btn-outline-primary mb-0">
              Explore Features
            </a>
          @else
            <a href="{{ route('dashboard') }}" class="btn bg-gradient-primary mb-0">
              Go to Dashboard
            </a>
          @endguest
        </div>

        <div class="row mt-4 g-3">
          <div class="col-12 col-sm-4">
            <div class="card mb-0">
              <div class="card-body py-3">
                <div class="text-xs text-uppercase text-secondary font-weight-bolder">1-click</div>
                <div class="h6 font-weight-bolder mb-0">Attendance</div>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-4">
            <div class="card mb-0">
              <div class="card-body py-3">
                <div class="text-xs text-uppercase text-secondary font-weight-bolder">Auto</div>
                <div class="h6 font-weight-bolder mb-0">Compliance alerts</div>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-4">
            <div class="card mb-0">
              <div class="card-body py-3">
                <div class="text-xs text-uppercase text-secondary font-weight-bolder">Role</div>
                <div class="h6 font-weight-bolder mb-0">Dashboards</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6 mt-5 mt-lg-0">
        <div class="card shadow-lg">
          <div class="card-header pb-0">
            <div class="d-flex align-items-center gap-3">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="bi bi-graph-up-arrow text-white"></i>
              </div>
              <div>
                <h6 class="mb-0">Today’s snapshot</h6>
                <p class="text-sm text-secondary mb-0">A clean view, like your dashboard</p>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4">
                <div class="card card-stats mb-0">
                  <div class="card-body py-3">
                    <div class="numbers">
                      <p class="mb-0">Avg. hours</p>
                      <h4 class="font-weight-bolder mb-0">8h</h4>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card card-stats mb-0">
                  <div class="card-body py-3">
                    <div class="numbers">
                      <p class="mb-0">Leaves</p>
                      <h4 class="font-weight-bolder mb-0">12</h4>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card card-stats mb-0">
                  <div class="card-body py-3">
                    <div class="numbers">
                      <p class="mb-0">Alerts</p>
                      <h4 class="font-weight-bolder mb-0">3</h4>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="alert alert-info text-white mt-4 mb-0" role="alert" style="background:linear-gradient(195deg,#5e72e4,#825ee4);border:0;">
              Pro tip: login to view your role-based dashboard and alerts.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="pb-5">
  <div class="container">
    <div class="row mb-4">
      <div class="col-lg-8">
        <h2 class="font-weight-bolder mb-1">Built for speed, clarity, and compliance</h2>
        <p class="text-secondary mb-0">Everything you need to stay organised—without the admin overload.</p>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md mb-3">
              <i class="bi bi-clock-history text-white"></i>
            </div>
            <h6 class="font-weight-bolder">Attendance Tracking</h6>
            <p class="text-secondary mb-0">Fast check-in/out with daily records and working hours calculations.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md mb-3">
              <i class="bi bi-calendar2-check text-white"></i>
            </div>
            <h6 class="font-weight-bolder">Leave Management</h6>
            <p class="text-secondary mb-0">Apply, approve, and track leaves with a simple workflow.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md mb-3">
              <i class="bi bi-shield-check text-white"></i>
            </div>
            <h6 class="font-weight-bolder">Compliance Alerts</h6>
            <p class="text-secondary mb-0">Automated warnings for overtime, missed attendance, and contract expiry.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

