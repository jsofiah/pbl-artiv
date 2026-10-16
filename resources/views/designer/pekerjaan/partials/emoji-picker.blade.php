<div id="emoji-picker"
     class="hidden absolute bottom-14 right-0 bg-white rounded-2xl shadow-xl border border-slate-200 p-3 z-40 w-[280px]">
    <div class="grid grid-cols-8 gap-1 max-h-48 overflow-y-auto">
        @foreach (['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕','🤑','🤠','😈','👿','👹','👺','🤡','💩','👻','💀','👽','👾','🤖','🎃'] as $emoji)
            <button type="button"
                    onclick="insertEmoji('{{ $emoji }}')"
                    class="w-8 h-8 rounded-lg hover:bg-slate-100 text-lg flex items-center justify-center transition">
                {{ $emoji }}
            </button>
        @endforeach
    </div>
</div>