@component('mail::message')
# Requested Code Reviews
@if(!empty($data['requested_reviews']))
@foreach($data['requested_reviews'] as $review)
### Code reviews assigned to {{ $review['reviewer_name'] }}
@foreach($review['pull_requests'] as $pullRequest)
- **{{ $pullRequest['author_name'] }}** assigned [{{ $pullRequest['title'] }}]({{ $pullRequest['url'] }}) on {{ $pullRequest['repository_name'] }}
@endforeach

@endforeach
@else
Looks like there is nothing assigned for code review today.
@endif
<br />

---

<br />
# Reviewed Pull Requests
@if(!empty($data['reviews']))
@foreach($data['reviews'] as $review)
### Code reviews on PRs created by {{ $review['author_name'] }}
@foreach($review['pull_requests'] as $pullRequest)
- **{{ $pullRequest['reviewer_name'] }}** {{ $pullRequest['status'] }} [{{ $pullRequest['title'] }}]({{ $pullRequest['url'] }}) on {{ $pullRequest['repository_name'] }}
@endforeach

@endforeach
@else
Looks like there is nothing assigned for code review today.
@endif
@endcomponent
