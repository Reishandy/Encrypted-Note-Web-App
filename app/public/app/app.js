// Caller
checkSession()

// Functions
async function checkSession() {
    // Prepare the request body
    let formData = new FormData();
    formData.append('type', 'check_session');

    try {
        let response = await fetch('../../logic/handler.php', {
            method: 'POST',
            body: formData
        });
        let data = await response.json();

        if (data.status !== 'authenticated') {
            window.location = '../auth/auth.html';
        }
    } catch (error) {
        // TODO: Make proper error handling
        alert(error);
    }
}

async function signOut() {
// Prepare the request body
    let formData = new FormData();
    formData.append('type', 'sign_out');

    try {
        let response = await fetch('../../logic/handler.php', {
            method: 'POST',
            body: formData
        });
        let data = await response.json();

        if (data.status === 'sign_out_success') {
            window.location = '../auth/auth.html';
        }
    } catch (error) {
        // TODO: Make proper error handling
        alert(error);
    }
}
