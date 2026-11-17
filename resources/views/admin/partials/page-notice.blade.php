@php($notice = isset($adminPageNotices) ? ($adminPageNotices[$noticeKey] ?? '') : '')
@if(filled($notice))
    <div class="alert alert-info border-0 shadow-sm mb-4" role="status">{!! nl2br(e($notice)) !!}</div>
@endif
