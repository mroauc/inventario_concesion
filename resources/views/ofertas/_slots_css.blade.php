<style>
    .foto-slots {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .foto-slot {
        position: relative;
        width: 150px;
    }

    /* El input real se oculta: el label es la superficie clicable */
    .foto-slot__input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        overflow: hidden;
    }

    .foto-slot__label {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 150px;
        height: 150px;
        margin: 0;
        cursor: pointer;
        overflow: hidden;
        border: 2px dashed #ced4da;
        border-radius: 8px;
        background: #f8f9fa;
        color: #8a929a;
        transition: border-color .18s ease, background .18s ease, color .18s ease;
    }

    .foto-slot__label:hover {
        border-color: #132a56;
        background: #eef1f6;
        color: #132a56;
    }

    /* Foco por teclado: el input está oculto, así que se marca el label */
    .foto-slot__input:focus ~ .foto-slot__label {
        border-color: #132a56;
        box-shadow: 0 0 0 .2rem rgba(19, 42, 86, .25);
    }

    .foto-slot--llena .foto-slot__label {
        border-style: solid;
        border-color: #dee2e6;
        background: #fff;
    }

    .foto-slot__img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .foto-slot__vacio {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .35rem;
        font-size: 1.75rem;
        line-height: 1;
    }

    .foto-slot__texto {
        font-size: .75rem;
        font-weight: 500;
    }

    .foto-slot__num {
        position: absolute;
        top: 6px;
        left: 6px;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 10px;
        background: rgba(19, 42, 86, .75);
        color: #fff;
        font-size: .7rem;
        font-weight: 700;
        line-height: 20px;
        text-align: center;
    }

    .foto-slot__quitar {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 22px;
        height: 22px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: rgba(220, 53, 69, .9);
        color: #fff;
        font-size: 1rem;
        line-height: 1;
        cursor: pointer;
    }

    .foto-slot__quitar:hover { background: #dc3545; }

    .foto-slot__nombre {
        max-width: 150px;
        margin-top: .25rem;
        font-size: .7rem;
    }

    @media (max-width: 575.98px) {
        .foto-slot,
        .foto-slot__label { width: 100px; }
        .foto-slot__label { height: 100px; }
        .foto-slot__nombre { max-width: 100px; }
    }
</style>
