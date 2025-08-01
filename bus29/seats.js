document.querySelectorAll('.seat').forEach(seat => {
    seat.addEventListener('click', function () {
        if (this.classList.contains('booked')) return;
        this.classList.toggle('selected');
    });
});
