// Variables
let noteCount = 0;

// Caller
checkSession();
getNotes();

// Doc functions
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
        callModalError(error);
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
        callModalError(error)
    }
}


// Modal functions
function callModalError(error) {
    let modalTitle = document.getElementById('util-modal-title');
    let modalContent = document.getElementById('util-modal-content');
    let modalBtn = document.getElementById('util-modal-btn');
    let modalClose = document.getElementById('util-modal-btn-close');

    modalTitle.textContent = 'Error';
    modalTitle.style.color = 'red';
    modalContent.textContent = JSON.stringify(error);
    modalClose.textContent = 'Close';

    // disable sign-out button
    modalBtn.style.opacity = '0';
    modalBtn.style.pointerEvents = 'none';

    let utilModal = new bootstrap.Modal(document.getElementById('util-modal'));
    utilModal.show();
}

function callModalSuccess(message) {
    let modalTitle = document.getElementById('util-modal-title');
    let modalContent = document.getElementById('util-modal-content');
    let modalBtn = document.getElementById('util-modal-btn');
    let modalClose = document.getElementById('util-modal-btn-close');

    modalTitle.textContent = 'Success';
    modalTitle.style.color = 'green';
    modalContent.textContent = message;
    modalClose.textContent = 'Close';

    // disable sign-out button
    modalBtn.style.opacity = '0';
    modalBtn.style.pointerEvents = 'none';

    let utilModal = new bootstrap.Modal(document.getElementById('util-modal'));
    utilModal.show();
}

function callModalSignOut() {
    let modalTitle = document.getElementById('util-modal-title');
    let modalContent = document.getElementById('util-modal-content');
    let modalBtn = document.getElementById('util-modal-btn');
    let modalClose = document.getElementById('util-modal-btn-close');

    modalTitle.textContent = 'Sign Out';
    modalTitle.style.color = 'red';
    modalContent.textContent = 'Are you sure you want to sign out?';
    modalClose.textContent = 'Cancel';

    // enable sign out button
    modalBtn.style.opacity = '1';
    modalBtn.style.pointerEvents = 'auto';
    modalBtn.onclick = function () {
        signOut();
    };

    let utilModal = new bootstrap.Modal(document.getElementById('util-modal'));
    utilModal.show();
}

function callModalDelete(noteId) {
    // Close the view modal
    let viewModal = bootstrap.Modal.getInstance(document.getElementById('view-modal'));
    viewModal.hide();

    let modalTitle = document.getElementById('util-modal-title');
    let modalContent = document.getElementById('util-modal-content');
    let noteTitle = document.getElementById('note-title-' + noteId).textContent;
    let modalBtn = document.getElementById('util-modal-btn');
    let modalClose = document.getElementById('util-modal-btn-close');

    modalTitle.textContent = 'Delete Note';
    modalContent.textContent = 'Are you sure you want to delete the note "' + noteTitle + '"?';
    modalClose.textContent = 'Cancel';

    // enable delete button
    modalBtn.style.opacity = '1';
    modalBtn.style.pointerEvents = 'auto';
    modalBtn.onclick = function () {
        deleteNote(noteId);
    }
    modalBtn.textContent = 'Delete';

    let utilModal = new bootstrap.Modal(document.getElementById('util-modal'));
    utilModal.show();
}

function callModalEdit(noteId, noteTitle, noteContent) {
    // Close the view modal
    let viewModal = bootstrap.Modal.getInstance(document.getElementById('view-modal'));
    viewModal.hide();

    let modalTitle = document.getElementById('edit-modal-title');
    let modalContent = document.getElementById('edit-modal-content');
    let modalBtn = document.getElementById('edit-modal-btn');

    modalTitle.value = noteTitle;
    modalContent.value = noteContent;

    modalBtn.onclick = function () {
        updateNote(noteId, modalTitle.value, modalContent.value);
    }

    let editModal = new bootstrap.Modal(document.getElementById('edit-modal'));
    editModal.show();
}

function callModalView(noteId, dateCreated, dateModified) {
    // View modal elements
    let modalTitle = document.getElementById('view-modal-title');
    let modalContent = document.getElementById('view-modal-content');
    let modalDate = document.getElementById('view-modal-date');
    let modalId = document.getElementById('view-modal-id');
    let modalDeleteBtn = document.getElementById('view-modal-delete-btn');
    let modalEditBtn = document.getElementById('view-modal-edit-btn');

    // Note elements
    let noteTitle = document.getElementById('note-title-' + noteId).textContent;
    let noteContent = document.getElementById('note-content-' + noteId).textContent;

    // Set the note details
    modalTitle.textContent = noteTitle;
    modalContent.textContent = noteContent;
    modalContent.innerHTML = modalContent.innerHTML.replace(/\n/g, '<br>');
    modalDate.textContent = 'Created: ' + dateCreated + ' | Modified: ' + dateModified;
    modalId.textContent = noteId + ' | ';

    // Set the buttons
    modalDeleteBtn.onclick = function () {
        callModalDelete(noteId);
    };
    modalEditBtn.onclick = function () {
        callModalEdit(noteId, noteTitle, noteContent)
    }

    let viewModal = new bootstrap.Modal(document.getElementById('view-modal'));
    viewModal.show();
}


// Note functions
async function updateNote(noteId, noteTitle, noteContent) {
    // Close the edit modal
    let editModal = bootstrap.Modal.getInstance(document.getElementById('edit-modal'));
    editModal.hide();

    // Prepare the request body
    let formData = new FormData();
    formData.append('type', 'update_note');
    formData.append('note_id', noteId);
    formData.append('title', noteTitle);
    formData.append('note', noteContent);

    try {
        let response = await fetch('../../logic/handler.php', {
            method: 'POST',
            body: formData
        });
        let data = await response.json();

        if (data.status === 'success') {
            // Update the notes
            document.getElementById('div-note-list').innerHTML = '';
            noteCount = 0;
            getNotes();

            callModalSuccess(data.message);
        } else {
            callModalError(data.message);
        }
    } catch (error) {
        callModalError(error);
    }

}

async function deleteNote(noteId) {
    // Close the util modal
    let utilModal = bootstrap.Modal.getInstance(document.getElementById('util-modal'));
    utilModal.hide();

    // Prepare the request body
    let formData = new FormData();
    formData.append('type', 'delete_note');
    formData.append('note_id', noteId);

    try {
        let response = await fetch('../../logic/handler.php', {
            method: 'POST',
            body: formData
        });
        let data = await response.json();

        if (data.status === 'success') {
            // Update the notes
            document.getElementById('div-note-list').innerHTML = '';
            noteCount = 0;
            getNotes();

            callModalSuccess(data.message);
        } else {
            callModalError(data.message);
        }
    } catch (error) {
        callModalError(error);
    }
}

async function addNote() {
    // Close the add modal
    let addModal = bootstrap.Modal.getInstance(document.getElementById('add-modal'));
    addModal.hide();

    // Get the note details
    let noteTitle = document.getElementById('add-modal-title').value;
    let noteContent = document.getElementById('add-modal-content').value;

    if (noteTitle === '') {
        noteTitle = 'Untitled';
    }

    // Prepare the request body
    let formData = new FormData();
    formData.append('type', 'add_note');
    formData.append('title', noteTitle);
    formData.append('note', noteContent);

    try {
        let response = await fetch('../../logic/handler.php', {
            method: 'POST',
            body: formData
        });
        let data = await response.json();

        if (data.status === 'success') {
            // Update the notes
            document.getElementById('div-note-list').innerHTML = '';
            noteCount = 0;
            getNotes();

            // Reset the form
            document.getElementById('add-modal-title').value = '';
            document.getElementById('add-modal-content').value = '';

            callModalSuccess(data.message);
        } else {
            callModalError(data.message);
        }
    } catch (error) {
        callModalError(error);
        console.log(error);
    }

}

async function getNotes() {
    // Prepare the request body
    let formData = new FormData();
    formData.append('type', 'get_notes');

    try {
        let response = await fetch('../../logic/handler.php', {
            method: 'POST',
            body: formData
        });
        let data = await response.json();

        if (data.status === 'success') {
            // Add the notes to the list
            for (let i = 0; i < data.notes.length; i++) {
                addNoteList(data.notes[i].id, data.notes[i].date_created, data.notes[i].date_modified, data.notes[i].title, data.notes[i].note);
            }
        } else {
            callModalError(data.message);
        }

        addNoteButton();
    } catch (error) {
        callModalError(error);
    }
}

function addNoteList(noteId, dateCreated, dateModified, noteTitle, noteContent) {
    // Create the note elements
    let note = document.createElement('div');
    note.className = 'note mb-4 p-3 note-hidden';

    let noteTitleElement = document.createElement('h2');
    noteTitleElement.className = 'roboto-bold';
    noteTitleElement.id = 'note-title-' + noteId;
    noteTitleElement.textContent = noteTitle;

    let noteContentElement = document.createElement('p');
    noteContentElement.className = 'roboto-thin';
    noteContentElement.id = 'note-content-' + noteId;
    noteContentElement.textContent = noteContent;
    noteContentElement.innerHTML = noteContentElement.innerHTML.replace(/\n/g, '<br>');

    let noteButton = document.createElement('button');
    noteButton.type = 'button';
    noteButton.className = 'btn btn-outline-light w-100';
    noteButton.onclick = function () {
        callModalView(noteId, dateCreated, dateModified);
    };
    noteButton.textContent = 'View';

    // Append the note elements
    note.appendChild(noteTitleElement);
    note.appendChild(noteContentElement);
    note.appendChild(noteButton);

    let layoutDiv = document.createElement('div');
    layoutDiv.appendChild(note);

    document.getElementById('div-note-list').appendChild(layoutDiv);

    // Animation sequential delay
    setTimeout(() => {
        note.classList.remove('note-hidden');
    }, 100 * noteCount++);
}

function addNoteButton() {
    let layoutDiv = document.createElement('div');
    layoutDiv.className = 'd-flex justify-content-center align-content-center';
    layoutDiv.style.height = '278px';

    let noteButton = document.createElement('button');
    noteButton.type = 'button';
    noteButton.className = 'btn btn-outline-light w-100 note-hidden note-add-button';
    noteButton.textContent = 'Add Note';

    // Add bs attributes
    noteButton.setAttribute('data-bs-toggle', 'modal');
    noteButton.setAttribute('data-bs-target', '#add-modal');

    layoutDiv.appendChild(noteButton);
    document.getElementById('div-note-list').appendChild(layoutDiv);

    setTimeout(() => {
        noteButton.classList.remove('note-hidden');
    }, 100 * noteCount++);
}

// Adjust textarea to fit content
let textarea = document.querySelectorAll('textarea');
textarea.forEach(textarea => {
    textarea.addEventListener('input', autoResize, false);
});

function autoResize() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
}

let editModal = document.getElementById('edit-modal');
editModal.addEventListener('shown.bs.modal', function () {
    let textarea = this.querySelector('textarea');
    textarea.dispatchEvent(new Event('input'));
});