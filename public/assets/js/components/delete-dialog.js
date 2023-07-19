function deleteDialog(id, url, csrf) {
    Swal.fire({
        title: "Yakin Menghapus Data?",
        text: "Perubahan tidak dapat dikembalikan lagi!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus Data",
        cancelButtonText: "Batalkan",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "DELETE",
                url: url,
                data: {
                    _token: csrf,
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: "Sukses",
                        text: "Data berhasil dihapus",
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function (err) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: err.message,
                    });
                    console.log(err);
                },
            });
        }
    });
}
