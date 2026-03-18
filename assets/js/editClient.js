document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('clientForm');
    if (form) {
        const clientName = document.getElementById('client_name');
        const clientId = document.getElementById('client_id');
        form.addEventListener('submit', function(e) {
            document.getElementById('feedback-container').classList.add('d-none');
            e.preventDefault();
            saveClient(clientName.value, clientId.value);
        });
    }
});

function saveClient(clientName, clientId) {
    const feedbackContainer = document.getElementById('feedback-container');
    const feedbackMessage = document.getElementById('feedback-message');
    console.log("Saving client:", clientName);
    
    // Send to API
    fetch('/api/client/update/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            client_id: clientId,
            client_name: clientName,
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Success:', data);
        if (data.success && data.status == '200') {
            feedbackContainer.classList.remove('d-none');
            feedbackContainer.classList.add('alert-success');
            feedbackMessage.textContent = 'Client saved successfully.';
            // Redirect to home page
            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        } else {
            feedbackContainer.classList.remove('d-none');
            feedbackContainer.classList.add('alert-danger');
            feedbackMessage.textContent = data.error;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        feedbackContainer.classList.remove('d-none');
        feedbackContainer.classList.add('alert-danger');
        feedbackMessage.textContent = 'An error occurred while saving the client.';
    });
}
