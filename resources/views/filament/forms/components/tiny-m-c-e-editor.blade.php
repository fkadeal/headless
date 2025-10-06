<textarea
    id="{{ $getId() }}"
    {!! $attributes->merge(['class' => 'w-full border-gray-300 rounded-md shadow-sm p-2']) !!}
>{{ old($getName(), $getState()) }}</textarea>

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#{{ $getId() }}',
        plugins: 'lists link image media code',
        toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | code',
        height: 500,
        setup: function(editor) {
            editor.on('Change', function() {
                @this.set('{{ $getName() }}', editor.getContent())
            });
        },
        // Media upload
        images_upload_url: '{{ route("ckeditor.upload") }}',
        automatic_uploads: true,
        images_upload_handler: function(blobInfo, success, failure) {
            let formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());

            fetch('{{ route("ckeditor.upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    if (res.location) success(res.location);
                    else failure('Upload failed');
                })
                .catch(() => failure('Upload failed'));
        }
    });
</script>
@endpush