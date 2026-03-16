export type MonthFormat = 'long' | 'short' | 'narrow';

export const MONTHS_BG_FULL: Record<number, string> = {
    1: 'януари',
    2: 'февруари',
    3: 'март',
    4: 'април',
    5: 'май',
    6: 'юни',
    7: 'юли',
    8: 'август',
    9: 'септември',
    10: 'октомври',
    11: 'ноември',
    12: 'декември',
};

export const MONTHS_BG_SHORT: Record<number, string> = {
    1: 'ян.',
    2: 'фев.',
    3: 'мар.',
    4: 'апр.',
    5: 'май',
    6: 'юни',
    7: 'юли',
    8: 'авг.',
    9: 'сеп.',
    10: 'окт.',
    11: 'ное.',
    12: 'дек.',
};

export const MONTHS_BG_NARROW: Record<number, string> = {
    1: '01',
    2: '02',
    3: '03',
    4: '04',
    5: '05',
    6: '06',
    7: '07',
    8: '08',
    9: '09',
    10: '10',
    11: '11',
    12: '12',
};




/**
 * monthIndex: 1 = януари, 12 = декември
 */
export function monthNameBg(
    monthIndex: number,
    format: MonthFormat = 'long',
    titleCase: boolean = true,
): string {
    if (!Number.isInteger(monthIndex) || monthIndex < 1 || monthIndex > 12) {
        throw new RangeError('monthIndex must be an integer between 1 and 12');
    }

    if (format === 'long') {
        return titleCase
            ? MONTHS_BG_FULL[monthIndex].charAt(0).toUpperCase() +
                  MONTHS_BG_FULL[monthIndex].slice(1)
            : MONTHS_BG_FULL[monthIndex];
    }

    if (format === 'narrow') {
        return MONTHS_BG_NARROW[monthIndex];
    }

    if (format === 'short') {
        return titleCase
            ? MONTHS_BG_SHORT[monthIndex].charAt(0).toUpperCase() +
                  MONTHS_BG_SHORT[monthIndex].slice(1)
            : MONTHS_BG_SHORT[monthIndex];
    }

    return '';
}
