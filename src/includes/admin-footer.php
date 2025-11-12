</div><!-- End admin-content -->
    
    <!-- Footer -->
    <footer class="admin-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                <?php echo COPYRIGHT; ?>
            </div>
            <div class="text-muted small">
                <?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?>
            </div>
        </div>
    </footer>
    
</div><!-- End admin-main -->

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin Scripts -->
<script>
// Sidebar toggle for mobile
const sidebarToggle = document.getElementById('sidebarToggle');
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        document.querySelector('.admin-sidebar').classList.toggle('show');
    });
}

// Auto dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>

</body>
</html>