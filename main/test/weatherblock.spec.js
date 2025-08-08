

import { createNewPost, insertBlock, wpDataSelect, enablePageDialogAccept, getEditedPostContent, getCurrentPostContent } from '@wordpress/e2e-test-utils'

jest.setTimeout(30000)

describe('Pruebas en bloqueclima', () => {
    beforeAll( async () => {
        await enablePageDialogAccept();
    })
    
    beforeEach( async () => {
        await createNewPost()
    })

    it('deberia insertar el bloque en el editor', async () => {

        
        await insertBlock( 'Bloqueclima' );
        
	    expect(await page.$( '[data-type="create-block/bloqueclima"]' ) ).not.toBeNull();

        console.log(await wpDataSelect())

        expect ( await getEditedPostContent() ).toEqual('<!-- wp:create-block/bloqueclima /-->')
	
    })

    

})