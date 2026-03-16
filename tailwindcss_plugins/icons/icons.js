import plugin from 'tailwindcss/plugin';
import icons from './values.js';

// запазваме логиката
function encodeSvg(svg) {
    return svg.replace('<svg', (~svg.indexOf('xmlns') ? '<svg' : '<svg xmlns="http://www.w3.org/2000/svg"'))
        .replace(/"/g, '\'')
        .replace(/%/g, '%25')
        .replace(/#/g, '%23')
        .replace(/{/g, '%7B')
        .replace(/}/g, '%7D')
        .replace(/</g, '%3C')
        .replace(/>/g, '%3E');
}

const getIconsStyles = (svgString) => {
    let svg = icons[svgString];
    const mode = svg.includes('currentColor') ? 'mask' : 'background-img';
    const uri = `url("data:image/svg+xml;utf8,${encodeSvg(svg)}")`;

    if (mode === 'mask') {
        return {
            mask: `${uri} no-repeat`,
            maskSize: '100% 100%',
            backgroundColor: 'currentColor',
            height: '1em',
            width: '1em',
        };
    } else {
        return {
            background: `${uri} no-repeat`,
            backgroundSize: '100% 100%',
            backgroundColor: 'transparent',
            height: '1em',
            width: '1em',
        };
    }
};

const values = (iconsObj) => {
    let res = {};
    Object.keys(iconsObj).forEach((key) => {
        res[key] = key;
    });
    return res;
};

export default plugin(function ({ matchUtilities, addBase }) {
        matchUtilities(
            {
                i: (value) => getIconsStyles(value),
            },
            { values: values(icons) }
        );

        addBase({
            'span[class^="i-"], span[class*=" i-"], i[class^="i-"], i[class*=" i-"]': {
                display: 'inline-block',
                'vertical-align': '-0.15em',
            },
        });
    });
