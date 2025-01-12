<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- Main Content -->
    @include('livewire.partials.connections-grid')

    <!-- Add Connection Button -->
    @include('livewire.partials.add-connection-button')

    <!-- New Connection Modal -->
    @include('livewire.partials.connection-modal', [
        'show' => '$wire.showNewModal',
        'title' => 'New SSH Connection',
        'action' => 'addConnection',
        'buttonText' => 'Add Connection'
    ])

    <!-- Edit Connection Modal -->
    @include('livewire.partials.connection-modal', [
        'show' => '$wire.showEditModal',
        'title' => 'Edit SSH Connection',
        'action' => 'updateConnection',
        'buttonText' => 'Update Connection'
    ])

    <!-- Actions Modal -->
    @include('livewire.partials.actions-modal')

    <!-- SSH Key Manager -->
    <div class="p-4">
        <livewire:ssh-key-manager />
    </div>
</div>