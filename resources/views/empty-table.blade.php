<tr class="empty-table-row" style="{{ $dataLength > 0 ? 'display: none;' : '' }}">
    <td colspan="{{ $colspan ?? 1 }}" class="text-center py-5">
        <div class="text-muted">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <h5>No hay datos disponibles</h5>
            <p>{{ $personalizedMessage ?? 'Ajusta los filtros para mostrar información' }}</p>
        </div>
    </td>
</tr>
