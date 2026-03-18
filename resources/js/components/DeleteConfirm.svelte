<!--
    DeleteConfirm.svelte — Svelte 5 delete confirmation modal

    Purpose:
    Renders a "Delete" button per table row. When clicked, it shows an animated
    modal dialog asking the user to confirm before the record is permanently deleted.

    Props (passed from Blade via data-svelte-props JSON):
    - recordId   {number}  ID of the record to delete
    - recordName {string}  Full name shown in the confirmation message
    - deleteUrl  {string}  The DELETE route URL (e.g. /records/5)
    - csrfToken  {string}  Laravel CSRF token for the hidden _token input

    Security:
    - The form uses POST with a _method=DELETE hidden field (Laravel method spoofing)
      because HTML forms only support GET/POST natively.
    - The csrfToken prop ensures the request passes Laravel's CSRF middleware check.
    - Clicking outside the modal (backdrop) or pressing Escape closes it safely
      without submitting the form.
-->

<script>
    // Destructure props passed in from the Blade view
    let { recordId, recordName, deleteUrl, csrfToken } = $props();

    // Reactive state — controls whether the modal is visible
    let showModal = $state(false);

    function openModal()  { showModal = true;  }
    function closeModal() { showModal = false; }

    // Allow keyboard users to dismiss the modal with Escape
    function handleKeydown(e) {
        if (e.key === 'Escape') closeModal();
    }
</script>

<!-- Listen for Escape key globally while this component is mounted -->
<svelte:window on:keydown={handleKeydown} />

<!-- ── Trigger button ─────────────────────────────────────────── -->
<button
    type="button"
    onclick={openModal}
    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition"
>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
    </svg>
    Delete
</button>

<!-- ── Confirmation modal (only rendered when showModal is true) ─ -->
{#if showModal}
    <!-- Semi-transparent backdrop — clicking it closes the modal -->
    <div
        class="fixed inset-0 z-40 bg-gray-500/75 transition-opacity"
        onclick={closeModal}
        role="presentation"
    ></div>

    <!-- Modal dialog — role="dialog" for screen reader accessibility -->
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
    >
        <div class="w-full max-w-md bg-white rounded-xl shadow-xl p-6">
            <!-- Warning icon + title -->
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-red-100">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 id="modal-title" class="text-base font-semibold text-gray-900">
                        Delete Record
                    </h3>
                    <!-- Show the record name so the user confirms the right entry -->
                    <p class="mt-1 text-sm text-gray-500">
                        Are you sure you want to delete <strong class="text-gray-700">{recordName}</strong>?
                        This action cannot be undone.
                    </p>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="mt-6 flex justify-end gap-3">
                <!-- Cancel — just closes the modal, no server request made -->
                <button
                    type="button"
                    onclick={closeModal}
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                >
                    Cancel
                </button>

                <!--
                    Confirm delete form.
                    Uses POST + _method=DELETE because HTML forms don't support DELETE.
                    The _token field satisfies Laravel's CSRF middleware check.
                    On submit the form POSTs to the delete route, which is
                    handled by RecordController@destroy using a PDO prepared statement.
                -->
                <form method="POST" action={deleteUrl}>
                    <input type="hidden" name="_token"  value={csrfToken} />
                    <input type="hidden" name="_method" value="DELETE" />
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
                    >
                        Yes, delete it
                    </button>
                </form>
            </div>
        </div>
    </div>
{/if}
