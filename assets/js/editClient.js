document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('clientForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('feedback-container').classList.add('d-none');
            const clientName = document.getElementById('client_name');
            const clientId = document.getElementById('client_id');
            const contactSelector = document.getElementById('contactSelector');
            let contactIdArray = [];
            if (contactSelector) {
                const selectedOption = contactSelector.options[contactSelector.selectedIndex];
                contactIdArray.push(selectedOption.value);
            }
            saveClient(clientName.value, clientId.value, contactIdArray);
        });
    }
});

function saveClient(clientName, clientId, contactIdArray) {
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
            contact_id: contactIdArray,
        })
    })
    .then(response => response.json())
    .then(data => {
        const contactSelectorModal = bootstrap.Modal.getInstance(document.getElementById('contactSelectorModal'));
        if (contactSelectorModal) {
            contactSelectorModal.hide();
        }
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
