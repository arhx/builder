<form class="auto-submit">
<div class="table-container mb-3">
    <table class="table">
        <thead>
        {{ $table->headers() }}
        </thead>
        <tbody>
        {{ $table->body() }}
        </tbody>
    </table>
</div>
<div class="flex">
    <div class="flex-1">

    </div>
    <div>
        {{ $table->links() }}
    </div>
</div>
</form>