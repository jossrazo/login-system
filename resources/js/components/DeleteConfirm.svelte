<script>
    let { recordId, recordName, deleteUrl, csrfToken } = $props();

    let showModal = $state(false);

    function openModal() {
        showModal = true;
    }

    function closeModal() {
        showModal = false;
    }

    function handleKeydown(e) {
        if (e.key === 'Escape') closeModal();
    }
</script>

<svelte:window on:keydown={handleKeydown} />

<!-- Delete trigger button -->
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

<!-- Confirmation modal -->
{#if showModal}
    <!-- Backdrop -->
    <div
        class="fixed inset-0 z-40 bg-gray-500/75 transition-opacity"
        onclick={closeModal}
        role="presentation"
    ></div>

    <!-- Dialog -->
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
    >
        <div class="w-full max-w-md bg-white rounded-xl shadow-xl p-6">
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
                    <p class="mt-1 text-sm text-gray-500">
                        Are you sure you want to delete <strong class="text-gray-700">{recordName}</strong>?
                        This action cannot be undone.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    onclick={closeModal}
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                >
                    Cancel
                </button>

                <!-- POST form with _method DELETE (CSRF protected) -->
                <form method="POST" action={deleteUrl}>
                    <input type="hidden" name="_token" value={csrfToken} />
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
