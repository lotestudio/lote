import { ref } from 'vue';

/**
 * Using fo simplifying list rendering with add and delete functionality.
 *  - Adding unique key to every element of list
 *  - Add item to list
 *  - Remove item to list
 *  - Get clean list without unique key
 *
 * @returns {{addItem: function, deleteItem: function, list: *}}
 * @param initial
 */

export function useEditableList(initial){

    let key = 0;
    const list = ref(initial);

    list.value.map((item)=>{
        item.unique_key =key++
        return item;
    });

    const addItem = (newItem) => {
        newItem.unique_key=key++;
        list.value.push(newItem);
    }

    const deleteItem = (key) => {
        const index = list.value.findIndex(
            (item) => Number(item.unique_key) === Number(key)
        );

        if (index > -1) {
            list.value.splice(index, 1);
        }
    }

    const getList= ()=>{
        let res =  JSON.parse(JSON.stringify(list.value));
        return res.map((item)=>{
            delete item.unique_key
            return item
        })
    }


    return { list, addItem, deleteItem, getList};
}
