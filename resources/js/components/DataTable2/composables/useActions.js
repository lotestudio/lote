export function useActions(state,ajax,defaultUrl){

    //This method removes JSON from url params and adds action (print, export).
    //Templates of a table for export or print must be implemented from the backend!!!
    const setAction = (action)=>{
        if (action === 'print' || action === 'export') {
            //skip json from query
            // eslint-disable-next-line @typescript-eslint/no-unused-vars
            let {json: _, ...params} = state.value.urlParams;
            const query = new URLSearchParams(params) + '&action='+action
            window.open(defaultUrl + '/?' + query);
        } else {
            state.value.urlParams.action = action;
            ajax();
        }
    }

    return {
        setAction
    }
}
