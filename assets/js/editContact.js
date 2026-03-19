document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('feedback-container').classList.add('d-none');
            const contactName = document.getElementById('contact_name');
            const contactSurname = document.getElementById('contact_surname');
            const contactEmail = document.getElementById('contact_email');
            const contactId = document.getElementById('contact_id');
            const clientSelector = document.getElementById('clientSelector');
            let clientIdArray = [];
            if (clientSelector) {
                const selectedOption = clientSelector.options[clientSelector.selectedIndex];
                clientIdArray.push(selectedOption.value);
            }
            saveContact(contactName.value, contactSurname.value, contactEmail.value, contactId.value, clientIdArray);
        });
    }
});

function saveContact(contactName, contactSurname, contactEmail, contactId, clientIdArray) {
    const feedbackContainer = document.getElementById('feedback-container');
    const feedbackMessage = document.getElementById('feedback-message');
    console.log("Saving contact:", contactName);

    if (!contactName || contactName.trim() === '') {
        feedbackContainer.classList.remove('d-none');
        feedbackContainer.classList.add('alert-danger');
        feedbackMessage.textContent = 'Contact name is required.';
        return;
    }

    if (!contactSurname || contactSurname.trim() === '') {
        feedbackContainer.classList.remove('d-none');
        feedbackContainer.classList.add('alert-danger');
        feedbackMessage.textContent = 'Contact surname is required.';
        return;
    }

    if (!contactEmail || contactEmail.trim() === '') {
        feedbackContainer.classList.remove('d-none');
        feedbackContainer.classList.add('alert-danger');
        feedbackMessage.textContent = 'Contact email is required.';
        return;
    }
    
    // Send to API
    fetch('/api/contact/update/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            contact_id: contactId,
            contact_name: contactName,
            contact_surname: contactSurname,
            contact_email: contactEmail,
            client_id: clientIdArray,
        })
    })
    .then(response => response.json())
    .then(data => {
        const clientSelectorModal = bootstrap.Modal.getInstance(document.getElementById('clientSelectorModal'));
        if (clientSelectorModal) {
            clientSelectorModal.hide();
        }
        if (data.success && data.status == '200') {
            feedbackContainer.classList.remove('d-none');
            feedbackContainer.classList.add('alert-success');
            feedbackMessage.textContent = 'Contact saved successfully.';
            // Redirect to home page
            setTimeout(() => {
                window.location.href = '/contacts';
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
        feedbackMessage.textContent = 'An error occurred while saving the contact.';
    });
}
