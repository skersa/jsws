{{--
    Recursive nested category <option> renderer for select2.
    Params:
      $nodes     : Collection of Category (roots), each with `childrenRecursive`
      $selected  : currently selected id (nullable)
      $depth     : current indentation depth (default 0)
      $excludeId : category id (and its descendants) to skip (nullable, for edit forms)
--}}
@php
    $depth     = $depth     ?? 0;
    $excludeId = $excludeId ?? null;
@endphp
@foreach($nodes as $node)
    @continue($excludeId && $node->id === $excludeId)
    <option value="{{ $node->id }}" {{ (string) $selected === (string) $node->id ? 'selected' : '' }}>
        {{ str_repeat('— ', $depth) }}{{ $node->name }}
    </option>
    @if($node->childrenRecursive && $node->childrenRecursive->isNotEmpty())
        @include('partials.category-select-options', [
            'nodes'     => $node->childrenRecursive,
            'selected'  => $selected,
            'depth'     => $depth + 1,
            'excludeId' => $excludeId,
        ])
    @endif
@endforeach
