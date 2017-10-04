@component('mail::message')
# Outstanding Reviews
@if(!empty($data['requested_reviews']))
@foreach($data['requested_reviews'] as $review)
### Assigned to {{ $review['reviewer_name'] }}
@foreach($review['pull_requests'] as $pullRequest)
- [{{ $pullRequest['title'] }}]({{ $pullRequest['url'] }}) on {{ $pullRequest['repository_name'] }} {{ $pullRequest['days'] }}
@endforeach

@endforeach
@else
Looks like there is nothing assigned for code review today.
@endif
<br />

---

<br />
# Completed Reviews
@if(!empty($data['reviews']))
@foreach($data['reviews'] as $review)
### {{ $review['author_name'] }}'s PRs
@foreach($review['pull_requests'] as $pullRequest)
@if($pullRequest['state'] === 'APPROVED')
- ✅ [{{ $pullRequest['title'] }}]({{ $pullRequest['url'] }}) on {{ $pullRequest['repository_name'] }}
@else
- ❌ [{{ $pullRequest['title'] }}]({{ $pullRequest['url'] }}) on {{ $pullRequest['repository_name'] }}
@endif
@endforeach

@endforeach
@else
Looks like there is nothing assigned for code review today.
@endif
@endcomponent
