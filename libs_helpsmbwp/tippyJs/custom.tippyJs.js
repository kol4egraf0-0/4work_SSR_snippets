
(function () {
  'use strict';

  const style = document.createElement('style');
  style.setAttribute('data-tippy-custom-animations', '');
  style.textContent = `
    /* tippy animation: scale-extreme */
    .tippy-box[data-animation=scale-extreme][data-placement^=top] {
      transform-origin: bottom;
    }
    .tippy-box[data-animation=scale-extreme][data-placement^=bottom] {
      transform-origin: top;
    }
    .tippy-box[data-animation=scale-extreme][data-placement^=left] {
      transform-origin: right;
    }
    .tippy-box[data-animation=scale-extreme][data-placement^=right] {
      transform-origin: left;
    }
    .tippy-box[data-animation=scale-extreme][data-state=hidden] {
      transform: scale(0);
      opacity: 0.25;
    }

    /* tippy animation: perspective-extreme */
    .tippy-box[data-animation=perspective-extreme][data-placement^=top] {
      transform-origin: bottom;
    }
    .tippy-box[data-animation=perspective-extreme][data-placement^=top][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective-extreme][data-placement^=top][data-state=hidden] {
      transform: perspective(700px) translateY(10px) rotateX(90deg);
    }

    .tippy-box[data-animation=perspective-extreme][data-placement^=bottom] {
      transform-origin: top;
    }
    .tippy-box[data-animation=perspective-extreme][data-placement^=bottom][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective-extreme][data-placement^=bottom][data-state=hidden] {
      transform: perspective(700px) translateY(-10px) rotateX(-90deg);
    }

    .tippy-box[data-animation=perspective-extreme][data-placement^=left] {
      transform-origin: right;
    }
    .tippy-box[data-animation=perspective-extreme][data-placement^=left][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective-extreme][data-placement^=left][data-state=hidden] {
      transform: perspective(700px) translateX(10px) rotateY(-90deg);
    }

    .tippy-box[data-animation=perspective-extreme][data-placement^=right] {
      transform-origin: left;
    }
    .tippy-box[data-animation=perspective-extreme][data-placement^=right][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective-extreme][data-placement^=right][data-state=hidden] {
      transform: perspective(700px) translateX(-10px) rotateY(90deg);
    }
    .tippy-box[data-animation=perspective-extreme][data-state=hidden] {
      opacity: 0.5;
    }

    /* tippy animation: perspective-subtle */
    .tippy-box[data-animation=perspective-subtle][data-placement^=top] {
      transform-origin: bottom;
    }
    .tippy-box[data-animation=perspective-subtle][data-placement^=top][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective-subtle][data-placement^=top][data-state=hidden] {
      transform: perspective(700px) translateY(5px) rotateX(30deg);
    }

    .tippy-box[data-animation=perspective-subtle][data-placement^=bottom] {
      transform-origin: top;
    }
    .tippy-box[data-animation=perspective-subtle][data-placement^=bottom][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective-subtle][data-placement^=bottom][data-state=hidden] {
      transform: perspective(700px) translateY(-5px) rotateX(-30deg);
    }

    .tippy-box[data-animation=perspective-subtle][data-placement^=left] {
      transform-origin: right;
    }
    .tippy-box[data-animation=perspective-subtle][data-placement^=left][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective-subtle][data-placement^=left][data-state=hidden] {
      transform: perspective(700px) translateX(5px) rotateY(-30deg);
    }

    .tippy-box[data-animation=perspective-subtle][data-placement^=right] {
      transform-origin: left;
    }
    .tippy-box[data-animation=perspective-subtle][data-placement^=right][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective-subtle][data-placement^=right][data-state=hidden] {
      transform: perspective(700px) translateX(-5px) rotateY(30deg);
    }
    .tippy-box[data-animation=perspective-subtle][data-state=hidden] {
      opacity: 0;
    }

    /* tippy animation: perspective (medium intensity) */
    .tippy-box[data-animation=perspective][data-placement^=top] {
      transform-origin: bottom;
    }
    .tippy-box[data-animation=perspective][data-placement^=top][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective][data-placement^=top][data-state=hidden] {
      transform: perspective(700px) translateY(8px) rotateX(60deg);
    }

    .tippy-box[data-animation=perspective][data-placement^=bottom] {
      transform-origin: top;
    }
    .tippy-box[data-animation=perspective][data-placement^=bottom][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective][data-placement^=bottom][data-state=hidden] {
      transform: perspective(700px) translateY(-8px) rotateX(-60deg);
    }

    .tippy-box[data-animation=perspective][data-placement^=left] {
      transform-origin: right;
    }
    .tippy-box[data-animation=perspective][data-placement^=left][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective][data-placement^=left][data-state=hidden] {
      transform: perspective(700px) translateX(8px) rotateY(-60deg);
    }

    .tippy-box[data-animation=perspective][data-placement^=right] {
      transform-origin: left;
    }
    .tippy-box[data-animation=perspective][data-placement^=right][data-state=visible] {
      transform: perspective(700px);
    }
    .tippy-box[data-animation=perspective][data-placement^=right][data-state=hidden] {
      transform: perspective(700px) translateX(-8px) rotateY(60deg);
    }
    .tippy-box[data-animation=perspective][data-state=hidden] {
      opacity: 0;
    }
  `;

  // inject styles into the document head (only once)
  if (!document.querySelector('style[data-tippy-custom-animations]')) {
    document.head.appendChild(style);
  }
})();