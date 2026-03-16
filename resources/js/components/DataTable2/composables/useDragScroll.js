import { onBeforeUnmount } from 'vue';

export function useDragScroll(element, direction = null) {
    element.style.userSelect = 'none';
    element.style.cursor = 'move';

    let mouseDown = false;
    let startX, startY, scrollLeft, scrollTop;
    let startDragging = function (e) {
        if (e.button !== 0) {
            return;
        }

        mouseDown = true;
        startX = e.pageX - element.offsetLeft;
        startY = e.pageY - element.offsetTop;
        scrollLeft = element.scrollLeft;
        scrollTop = element.scrollTop;
    };

    let stopDragging = function () {
        mouseDown = false;
    };

    element.addEventListener('mousemove', (e) => {
        e.preventDefault();
        if (!mouseDown) {
            return;
        }
        const x = e.pageX - element.offsetLeft;
        const y = e.pageY - element.offsetTop;
        if (!direction || direction === 'horizontal') {
            const scrollX = x - startX;
            element.scrollLeft = scrollLeft - scrollX;
        }
        if (!direction || direction === 'vertical') {
            const scrollY = y - startY;
            element.scrollTop = scrollTop - scrollY;
        }
    });

    element.addEventListener('mousedown', startDragging, false);
    element.addEventListener('mouseup', stopDragging, false);
    element.addEventListener('mouseleave', stopDragging, false);

    const reset = () => {
        element.removeEventListener('mousedown', startDragging);
        element.removeEventListener('mouseup', stopDragging);
        element.removeEventListener('mouseleave', stopDragging);
    };

    onBeforeUnmount(() => {
        reset();
    });
}
