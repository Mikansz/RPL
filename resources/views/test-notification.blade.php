@extends('layouts.app')

@section('title', 'Test Notification System')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Test Notification System</h5>
                </div>
                <div class="card-body">
                    <p>Click the buttons below to test different types of notifications:</p>
                    
                    <div class="d-grid gap-2 d-md-block">
                        <button type="button" class="btn btn-success" onclick="testNotification('success')">
                            Test Success Notification
                        </button>
                        <button type="button" class="btn btn-danger" onclick="testNotification('error')">
                            Test Error Notification
                        </button>
                        <button type="button" class="btn btn-warning" onclick="testNotification('warning')">
                            Test Warning Notification
                        </button>
                        <button type="button" class="btn btn-info" onclick="testNotification('info')">
                            Test Info Notification
                        </button>
                    </div>
                    
                    <hr>
                    
                    <h6>Test AJAX Request</h6>
                    <button type="button" class="btn btn-primary" onclick="testAjaxNotification()">
                        Test AJAX Notification
                    </button>

                    <hr>

                    <h6>Test Flash Message</h6>
                    <a href="/test-flash" class="btn btn-secondary">Test Flash Message (Redirect)</a>
                    <a href="/test-flash-page" class="btn btn-secondary">Test Flash Message (Direct)</a>

                    <hr>

                    <h6>Test Leave Approval Form</h6>
                    <form method="POST" action="/test-leave-approval" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">Test Approve Leave (URL Method)</button>
                    </form>

                    <hr>

                    <h6>Test URL Notification Directly</h6>
                    <a href="?approved=1&message=Test%20notifikasi%20langsung!" class="btn btn-outline-success">Test Approved Notification</a>
                    <a href="?rejected=1&message=Test%20notifikasi%20ditolak!" class="btn btn-outline-danger">Test Rejected Notification</a>

                    <hr>

                    <h6>Test Simple Toast</h6>
                    <button type="button" class="btn btn-outline-primary" onclick="testSimpleToast()">Test Simple Toast</button>
                    
                    <hr>
                    
                    <h6>Debug Information</h6>
                    <div id="debug-info">
                        <p><strong>showNotification function available:</strong> <span id="function-check"></span></p>
                        <p><strong>Flash container exists:</strong> <span id="container-check"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testNotification(type) {
    const messages = {
        'success': 'This is a success notification!',
        'error': 'This is an error notification!',
        'warning': 'This is a warning notification!',
        'info': 'This is an info notification!'
    };
    
    if (typeof window.showNotification === 'function') {
        window.showNotification(messages[type], type);
    } else {
        alert('showNotification function is not available!');
    }
}

function testSimpleToast() {
    if (typeof window.showSimpleToast === 'function') {
        window.showSimpleToast('Test simple toast notification!', 'success');
    } else {
        alert('showSimpleToast function not available');
    }
}

function testAjaxNotification() {
    // Simulate an AJAX request
    fetch('/test-ajax-notification', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof window.showNotification === 'function') {
                window.showNotification(data.message, 'success');
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof window.showNotification === 'function') {
            window.showNotification('AJAX request failed', 'error');
        } else {
            alert('AJAX request failed');
        }
    });
}

// Check debug information
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('function-check').textContent = 
        typeof window.showNotification === 'function' ? 'Yes' : 'No';
    
    document.getElementById('container-check').textContent = 
        document.getElementById('flash-messages') ? 'Yes' : 'No';
});
</script>
@endsection
