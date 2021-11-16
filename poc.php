<?php

$string = <<<MARKDOWN

# Paper Title

![enter image description here](https://placekitten.com/96/140)


-   **End**, if it can be summarized quickly early on (in the Introduction or Prelimina`enter code here`ries), or if sufficient comparisons require the technical content of the paper. In this case Related Work should appear just before the Conclusions, possibly in a more general section "Discussion and Related Work".
![lkjlkjlkjlkjlkjlkjlkjlkj](https://placekitten.com/408/287)
## The Body

![654646465465465465465465](https://placekitten.com/200/287)
-   I believe in putting papers on the web the minute they're finished. They should be dated and can be referenced as technical reports -- it's not necessary to have an actual technical report number. Never, ever put up a paper with a conference copyright notice when it's only been submitted, and never, ever reference a paper as "submitted to conference X." You're only asking for embarrassment when the paper is finally published in conference Y a year or two later.
MARKDOWN;

$pattern = '/\[?(!)(?\'alt\'\[[^\]\[]*\[?[^\]\[]*\]?[^\]\[]*)\]\((?\'url\'[^\s]+?)(?:\s+(["\'])(?\'title\'.*?)\4)?\)/';
$replacement = '$1,$3';

echo preg_replace_callback('/!\[(.*)\]\s?\((.*)()(.*)\)/', function ($match) {
    $file = rand(1, 1000).'.jpg';

    file_put_contents($file, file_get_contents($match[2]));

    return str_replace($match[2], $file, $match[0]);
}, $string);
