const accordionItems = document.querySelectorAll('.accordion');

function toggleAccordion(e) {
  if (!e.target.closest('.accordion__header')) return;

  const clickedItem = e.currentTarget;
  const body = clickedItem.querySelector('.accordion__body');

  if (clickedItem.classList.contains('accordion--expand')) {
    clickedItem.classList.remove('accordion--expand');
    body.style.maxHeight = null;
    return;
  }

  accordionItems.forEach(item => {
    const itemBody = item.querySelector('.accordion__body');
    itemBody.style.maxHeight = null;
    item.classList.remove('accordion--expand');
  });

  requestAnimationFrame(() => {
    clickedItem.classList.add('accordion--expand');
    body.style.maxHeight = (body.scrollHeight + 40) + '3px';
  });
}

accordionItems.forEach(item => {
  item.addEventListener('click', toggleAccordion);
});