// Add your JavaScript here

let btns = document.getElementsByClassName('btn-remove');

for (const btn of btns) {
    btn.addEventListener('click', (e) => {
        const idP = btn.dataset.id;
        $wire.isEmpty(idP).then((r) => {
            if(r){
                Swal.fire({
                    title: 'Eliminar Producto?',
                    text: 'Este proceso eliminara el producto permanentemente.',
                    icon: 'question',
                    confirmButtonText: 'Eliminar',
                    confirmButtonColor: 'red',
                    cancelButtonColor: 'gray',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if(result.isConfirmed){
                        $wire.deleteProduct(idP);
                    }
                });
            }else{
                Swal.fire({
                    title: 'No se puede Eliminar',
                    icon: 'error',
                    text: 'Este producto tiene ordenes enlazadas no se puede eliminar.'
                });
            }
        });
    })
}

