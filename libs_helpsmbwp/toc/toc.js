/**
 * находит кнопку переключения для указанного контейнера TOC
 */
function getToggleLink(container) {
  const wrapper = container.closest('[data-toc-wrapper]');
  return wrapper?.querySelector('[data-toc-toggle-btn]');
}

/**
 * свернуть / развернуть TOC
 */
function toggleToc(container, forceState = null) {
  const isExpanded = container.classList.contains('expanded');
  const shouldExpand = forceState !== null ? forceState : !isExpanded;
  const toggleBtn = getToggleLink(container);
  const toggleText = toggleBtn?.querySelector('[data-toggle-text]');

  if (shouldExpand) {
    container.classList.remove('collapsed');
    container.classList.add('expanded');
    if (toggleText) toggleText.textContent = 'Свернуть';
    if (toggleBtn) toggleBtn.classList.add('expanded');
  } else {
    container.classList.remove('expanded');
    container.classList.add('collapsed');
    if (toggleText) toggleText.textContent = 'Развернуть';
    if (toggleBtn) toggleBtn.classList.remove('expanded');
  }
}

/**
 * свернуть / развернуть TOC чтоб при сворачивании к концу TOC шло крч
 * function toggleToc(container, forceState = null) {
  const isExpanded = container.classList.contains('expanded');
  const shouldExpand = forceState !== null ? forceState : !isExpanded;
  const toggleLink = getToggleLink(container);

  if (shouldExpand) {
    container.classList.remove('collapsed');
    container.classList.add('expanded');
    if (toggleLink) toggleLink.textContent = 'Свернуть';

    setTimeout(() => {
      const containerBottom = container.offsetTop + container.offsetHeight;
      window.scrollTo({
        top: containerBottom - window.innerHeight + 100,
        behavior: 'smooth'
      });
    }, 150);
  } else {
    container.classList.remove('expanded');
    container.classList.add('collapsed');
    if (toggleLink) toggleLink.textContent = 'Развернуть';

    setTimeout(() => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }, 150);
  }
}
 */


/**
 * если высота контента меньше 250px контейне, нет так 
 */
document.querySelectorAll('[data-toc-container]').forEach(container => {
  const tocList = container.querySelector('[data-toc-list]');
  if (!tocList) return;

  const contentHeight = tocList.scrollHeight;
  const toggleBtn = getToggleLink(container);

  if (contentHeight > 250) {
    container.classList.add('collapsed'); //изначально display: block/inline/flex хз
  } else {
    container.classList.add('expanded');
    if (toggleBtn) toggleBtn.style.display = 'none'; 
  }
});

/**
 * сворачивание/разворачивание
 */
document.addEventListener('click', function (e) {
  const toggleBtn = e.target.closest('[data-toc-toggle-btn]');
  if (!toggleBtn) return;
  e.preventDefault();

  const wrapper = toggleBtn.closest('[data-toc-wrapper]');
  const container = wrapper?.querySelector('[data-toc-container]');
  if (!container) return;

  toggleToc(container);
});

/**
 * smooth переход к статье
 */
document.querySelectorAll('[data-toc-list] a[href^="#"]').forEach(anchor => {
  const targetId = anchor.getAttribute('href');
  const targetElement = document.querySelector(targetId);

  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    if (targetElement) {
      window.scrollTo({
        top: targetElement.offsetTop - 90, // хедер высота свою поставь 
        behavior: 'smooth'
      });
    }
  });
});