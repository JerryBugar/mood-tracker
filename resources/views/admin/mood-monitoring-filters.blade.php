<div class="filters">
    <form method="GET" action="{{ route('admin.mood-monitoring') }}" data-turbo="true">
        <div class="filter-row">
            <div class="form-group">
                <label for="division" class="form-label">Divisi</label>
                <select name="division" id="division" class="form-control" data-turbo="true">
                    <option value="">Semua Divisi</option>
                    <option value="IT" {{ request('division') === 'IT' ? 'selected' : '' }}>IT Department</option>
                    <option value="HR" {{ request('division') === 'HR' ? 'selected' : '' }}>HR Department</option>
                    <option value="Finance" {{ request('division') === 'Finance' ? 'selected' : '' }}>Finance Department</option>
                    <option value="Marketing" {{ request('division') === 'Marketing' ? 'selected' : '' }}>Marketing Department</option>
                </select>
            </div>

            <div class="form-group">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}" data-turbo="true">
            </div>

            <div class="form-group">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}" data-turbo="true">
            </div>

            <div class="form-group">
                <label for="mood" class="form-label">Mood</label>
                <select name="mood" id="mood" class="form-control" data-turbo="true">
                    <option value="">Semua Mood</option>
                    <option value="senyum" {{ request('mood') === 'senyum' ? 'selected' : '' }}>Senang</option>
                    <option value="sedih" {{ request('mood') === 'sedih' ? 'selected' : '' }}>Sedih</option>
                    <option value="lelah" {{ request('mood') === 'lelah' ? 'selected' : '' }}>Lelah</option>
                    <option value="marah" {{ request('mood') === 'marah' ? 'selected' : '' }}>Marah</option>
                    <option value="netral" {{ request('mood') === 'netral' ? 'selected' : '' }}>Biasa Saja</option>
                </select>
            </div>

            <div class="form-group" style="margin-top: auto;">
                <button type="submit" class="btn-filter">Filter</button>
            </div>
        </div>
    </form>
</div>