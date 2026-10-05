<!-- Footer Component -->
<footer class="text-center p-4 mt-5 text-muted">
    <p class="mb-0 small">&copy; 2026 Splash Mania Theme Park. All rights reserved.</p>
</footer>

<!-- External Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Global Script: Auto-dismiss alerts after 4 seconds -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Set a timer for 4000 milliseconds (4 seconds)
    setTimeout(function() {
        let alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alertElement) {
            // Safely close the alert with Bootstrap animation
            if (typeof bootstrap !== 'undefined') {
                let bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();
            } else {
                alertElement.style.display = 'none';
            }
        });
    }, 4000);
});
</script>
</body>
</html>