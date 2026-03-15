@if ($errors->any() || session('error'))
    <div class="alert alert-error mb-6 shadow-md border-l-4 border-error">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div class="flex flex-col">
            @if(session('error'))
                <span class="font-bold uppercase tracking-widest text-[10px] opacity-60 mb-1">System Error</span>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            @endif

            @if($errors->any())
                @if(session('error')) <div class="divider my-1 opacity-20"></div> @endif
                <span class="font-bold uppercase tracking-widest text-[10px] opacity-60 mb-1">Validation Errors</span>
                <ul class="list-disc list-inside text-sm font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif