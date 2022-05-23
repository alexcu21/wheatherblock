import { createNewPost, insertBlock } from '@wordpress/e2e-test-utils'

describe('Weather Block testing', () => {
    
    it('Should insert the block in the editor', async () => {

        await createNewPost()
        await insertBlock( 'Weather Block' );
        
	    expect( await page.$( '[data-type="create-block/weatherblock"]' ) ).not.toBeNull();
	
    })
})