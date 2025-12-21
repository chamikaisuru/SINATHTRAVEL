import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { 
  getAdminPackages, 
  createAdminPackage, 
  updateAdminPackage, 
  deleteAdminPackage,
  type AdminPackage 
} from "@/lib/adminApi";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useToast } from "@/hooks/use-toast";
import { Plus, Edit, Trash2 } from "lucide-react";
import { Badge } from "@/components/ui/badge";

const packageSchema = z.object({
  category: z.enum(['tour', 'visa', 'ticket', 'offer']),
  title_en: z.string().min(5, "Title required"),
  title_si: z.string().optional(),
  title_ta: z.string().optional(),
  description_en: z.string().min(10, "Description required"),
  description_si: z.string().optional(),
  description_ta: z.string().optional(),
  price: z.string().min(1, "Price required"),
  duration: z.string().optional(),
  status: z.enum(['active', 'inactive']),
});

type PackageFormData = z.infer<typeof packageSchema>;

export default function AdminPackages() {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [selectedImage, setSelectedImage] = useState<File | null>(null);
  const [editingPackage, setEditingPackage] = useState<AdminPackage | null>(null);

  // FIXED: Handle both array and object response formats
  const { data: packagesResponse, isLoading } = useQuery({
    queryKey: ['admin-packages'],
    queryFn: async () => {
      console.log('📦 Fetching packages...');
      const result = await getAdminPackages();
      console.log('📦 Raw result:', result);
      return result;
    },
  });

  // FIXED: Safely extract packages array from response
  const packages = Array.isArray(packagesResponse) 
    ? packagesResponse 
    : (packagesResponse?.data || packagesResponse?.packages || []);

  console.log('📦 Packages array:', packages);

  const form = useForm<PackageFormData>({
    resolver: zodResolver(packageSchema),
    defaultValues: {
      category: 'tour',
      status: 'active',
      title_en: '',
      description_en: '',
      price: '',
    },
  });

  const createMutation = useMutation({
    mutationFn: createAdminPackage,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-packages'] });
      toast({ title: "Package created successfully" });
      setDialogOpen(false);
      form.reset();
      setSelectedImage(null);
    },
    onError: (error: Error) => {
      toast({ title: "Failed to create package", description: error.message, variant: "destructive" });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: deleteAdminPackage,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-packages'] });
      toast({ title: "Package deleted successfully" });
    },
    onError: (error: Error) => {
      toast({ title: "Failed to delete package", description: error.message, variant: "destructive" });
    },
  });

  function onSubmit(values: PackageFormData) {
    const formData = new FormData();
    Object.entries(values).forEach(([key, value]) => {
      if (value) formData.append(key, value.toString());
    });
    
    if (selectedImage) {
      formData.append('image', selectedImage);
    }
    
    createMutation.mutate(formData);
  }

  function handleDelete(id: number) {
    if (confirm('Are you sure you want to delete this package?')) {
      deleteMutation.mutate(id);
    }
  }

  if (isLoading) {
    return (
      <div className="space-y-6">
        <h1 className="text-3xl font-heading font-bold text-primary">Packages</h1>
        <div className="text-center py-12">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
          <p className="mt-4 text-muted-foreground">Loading packages...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-heading font-bold text-primary">Packages</h1>
        <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
          <DialogTrigger asChild>
            <Button className="bg-secondary hover:bg-secondary/90">
              <Plus className="w-4 h-4 mr-2" />
              Add Package
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Add New Package</DialogTitle>
            </DialogHeader>
            <Form {...form}>
              <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <FormField
                    control={form.control}
                    name="category"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Category</FormLabel>
                        <Select onValueChange={field.onChange} defaultValue={field.value}>
                          <FormControl>
                            <SelectTrigger>
                              <SelectValue />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            <SelectItem value="tour">Tour</SelectItem>
                            <SelectItem value="visa">Visa</SelectItem>
                            <SelectItem value="ticket">Ticket</SelectItem>
                            <SelectItem value="offer">Offer</SelectItem>
                          </SelectContent>
                        </Select>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="status"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Status</FormLabel>
                        <Select onValueChange={field.onChange} defaultValue={field.value}>
                          <FormControl>
                            <SelectTrigger>
                              <SelectValue />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                          </SelectContent>
                        </Select>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>

                <FormField
                  control={form.control}
                  name="title_en"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Title (English)</FormLabel>
                      <FormControl>
                        <Input {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={form.control}
                  name="description_en"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Description (English)</FormLabel>
                      <FormControl>
                        <Textarea {...field} rows={3} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <div className="grid grid-cols-2 gap-4">
                  <FormField
                    control={form.control}
                    name="price"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Price (USD)</FormLabel>
                        <FormControl>
                          <Input type="number" step="0.01" {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="duration"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Duration</FormLabel>
                        <FormControl>
                          <Input placeholder="e.g. 5 Days" {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>

                <div>
                  <FormLabel>Image</FormLabel>
                  <Input 
                    type="file" 
                    accept="image/*"
                    onChange={(e) => setSelectedImage(e.target.files?.[0] || null)}
                  />
                </div>

                <Button type="submit" disabled={createMutation.isPending}>
                  {createMutation.isPending ? "Creating..." : "Create Package"}
                </Button>
              </form>
            </Form>
          </DialogContent>
        </Dialog>
      </div>

      {/* FIXED: Show message when no packages */}
      {!Array.isArray(packages) || packages.length === 0 ? (
        <Card>
          <CardContent className="py-12 text-center">
            <p className="text-muted-foreground mb-4">No packages found</p>
            <p className="text-sm text-muted-foreground">Click "Add Package" to create your first package</p>
          </CardContent>
        </Card>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {packages.map((pkg) => (
            <Card key={pkg.id}>
              {pkg.image && (
                <div className="h-48 overflow-hidden">
                  <img src={pkg.image} alt={pkg.title_en} className="w-full h-full object-cover" />
                </div>
              )}
              <CardHeader>
                <div className="flex items-start justify-between">
                  <CardTitle className="text-lg">{pkg.title_en}</CardTitle>
                  <Badge variant={pkg.status === 'active' ? 'default' : 'secondary'}>
                    {pkg.status}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent>
                <p className="text-sm text-muted-foreground line-clamp-2">{pkg.description_en}</p>
                <div className="mt-4 flex items-center justify-between">
                  <span className="text-xl font-bold text-primary">${pkg.price}</span>
                  {pkg.duration && (
                    <span className="text-sm text-muted-foreground">{pkg.duration}</span>
                  )}
                </div>
              </CardContent>
              <CardFooter className="flex gap-2">
                <Button variant="outline" size="sm" className="flex-1">
                  <Edit className="w-4 h-4 mr-1" />
                  Edit
                </Button>
                <Button 
                  variant="destructive" 
                  size="sm"
                  onClick={() => pkg.id && handleDelete(pkg.id)}
                >
                  <Trash2 className="w-4 h-4" />
                </Button>
              </CardFooter>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}