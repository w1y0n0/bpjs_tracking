// Main JavaScript for Employee Progress System

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Filter functionality for admin home
    $('.filter-btn').click(function() {
        var filter = $(this).data('filter');
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        if (filter === 'all') {
            $('tbody tr').show();
        } else {
            $('tbody tr').hide();
            $('tbody tr[data-status="' + filter + '"]').show();
        }
    });
    
    // Search functionality
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // NIK search for pegawai
    $('#nikSearch').on('input', function() {
        var nik = $(this).val().replace(/\s/g, ''); // Remove spaces
        if (nik.length >= 16) {
            searchByNIK(nik);
        }
    });
    
    // Checkbox change handlers for admin edit
    $('.status-checkbox').change(function() {
        var tahapanId = $(this).data('tahapan');
        var status = $(this).val();
        var reasonContainer = $('#reason-' + tahapanId);
        
        if (status === 'gagal') {
            reasonContainer.show();
            reasonContainer.find('input').prop('required', true);
        } else {
            reasonContainer.hide();
            reasonContainer.find('input').prop('required', false).val('');
        }
        
        // Update other checkboxes in the same group
        $('input[name="status[' + tahapanId + ']"]').not(this).prop('checked', false);
    });
    
    // Form validation
    $('form').submit(function(e) {
        var isValid = true;
        var errorMessage = '';
        
        // Check required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
                errorMessage += 'Field ' + $(this).attr('name') + ' is required.\n';
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // NIK validation
        var nikInputs = $(this).find('input[name*="nik"]');
        nikInputs.each(function() {
            var nik = $(this).val().replace(/\s/g, '');
            if (nik && nik.length !== 16) {
                isValid = false;
                $(this).addClass('is-invalid');
                errorMessage += 'NIK must be exactly 16 digits.\n';
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
        }
    });
    
    // Format NIK input
    $('input[name*="nik"]').on('input', function() {
        var value = $(this).val().replace(/\s/g, '');
        var formatted = value.replace(/(.{4})/g, '$1 ').trim();
        $(this).val(formatted);
    });
    
    // Confirm delete actions
    $('.delete-btn').click(function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });
    
    // Progress update animation
    function animateProgress() {
        $('.step-circle.current').addClass('animate__animated animate__pulse animate__infinite');
    }
    
    // Call animation function
    animateProgress();
});

// Search by NIK function
function searchByNIK(nik) {
    $.ajax({
        url: 'search_pegawai.php',
        method: 'POST',
        data: { nik: nik },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displaySearchResults(response.data);
            } else {
                $('#searchResults').html('<div class="alert alert-warning">Pegawai tidak ditemukan</div>');
            }
        },
        error: function() {
            $('#searchResults').html('<div class="alert alert-danger">Terjadi kesalahan saat mencari data</div>');
        }
    });
}

// Display search results
function displaySearchResults(data) {
    var html = '<div class="table-responsive"><table class="table table-striped">';
    html += '<thead><tr><th>Nama Pegawai</th><th>Nama Anggota Keluarga</th><th>Aksi</th></tr></thead>';
    html += '<tbody>';
    
    data.forEach(function(item) {
        html += '<tr>';
        html += '<td>' + item.nama_pegawai + '</td>';
        html += '<td>' + item.nama_anggota + '</td>';
        html += '<td><a href="lihat_progress.php?pegawai_id=' + item.pegawai_id + '" class="btn btn-primary btn-sm">Lihat Progress</a></td>';
        html += '</tr>';
    });
    
    html += '</tbody></table></div>';
    $('#searchResults').html(html);
}

// Update progress status
function updateProgressStatus(pegawaiId, tahapanId, status, alasan = '') {
    $.ajax({
        url: 'update_progress.php',
        method: 'POST',
        data: {
            pegawai_id: pegawaiId,
            tahapan_id: tahapanId,
            status: status,
            alasan_gagal: alasan,
            csrf_token: $('input[name="csrf_token"]').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Terjadi kesalahan saat mengupdate progress');
        }
    });
}

// Utility functions
function formatNIK(nik) {
    return nik.replace(/(.{4})/g, '$1 ').trim();
}

function validateNIK(nik) {
    var cleanNIK = nik.replace(/\s/g, '');
    return cleanNIK.length === 16 && /^\d+$/.test(cleanNIK);
}

// Export functions for global access
window.searchByNIK = searchByNIK;
window.updateProgressStatus = updateProgressStatus;
window.formatNIK = formatNIK;
window.validateNIK = validateNIK;

