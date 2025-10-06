<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field">
    <div
        x-data="{
            state: $wire.$entangle(@js($getStatePath())),
            initEditor() {
                ClassicEditor
                    .create(this.$refs.editor, {
                    })
                    .then(editor => {
                        this.editor = editor;

                        // Load the initial state
                        editor.setData(this.state || '');

                        // Keep Livewire state synced
                        editor.model.document.on('change:data', () => {
                            this.state = editor.getData();
                        });

                        // When Livewire updates (e.g. form reset), update the editor
                        this.$watch('state', (value) => {
                            if (value !== editor.getData()) {
                                editor.setData(value || '');
                            }
                        });
                    })
                    .catch(error => console.error(error));
            }
        }"
        x-init="initEditor()"
        {{ $getExtraAttributeBag() }}>
        <textarea x-ref="editor" x-cloak>
        {{ is_array($getState()) ? json_encode($getState(), JSON_UNESCAPED_UNICODE) : $getState() }}
        </textarea>
    </div>

    @once
    @push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    @endpush
    @endonce
</x-dynamic-component>